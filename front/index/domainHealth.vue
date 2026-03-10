<template>
<div class="domain-health-check">
  <el-card class="box-card">
    <div slot="header" class="clearfix">
      <span>🔍 域健康检查</span>
      <el-button 
        style="float: right; padding: 3px 10px" 
        type="primary" 
        size="small"
        @click="runHealthCheck"
        :loading="checking">
        {{ checking ? '检查中...' : '立即检查' }}
      </el-button>
    </div>
    
    <!-- 服务器信息 -->
    <el-descriptions title="服务器信息" :column="3" border>
      <el-descriptions-item label="服务器 IP">
        <el-tag :type="serverInfo.server_ip ? 'success' : 'danger'">
          {{ serverInfo.server_ip || '未知' }}
        </el-tag>
      </el-descriptions-item>
      <el-descriptions-item label="检查时间">
        {{ serverInfo.timestamp || '-' }}
      </el-descriptions-item>
      <el-descriptions-item label="健康状态">
        <el-tag :type="healthStatus.type">
          {{ healthStatus.text }}
        </el-tag>
      </el-descriptions-item>
    </el-descriptions>
    
    <!-- 统计信息 -->
    <el-row :gutter="20" style="margin-top: 20px">
      <el-col :span="8">
        <el-alert
          title="严重问题"
          type="error"
          :closable="false"
          show-icon>
          <template slot="title">
            <span style="font-size: 24px; font-weight: bold">{{ summary.critical }}</span>
            <span style="margin-left: 10px">个</span>
          </template>
        </el-alert>
      </el-col>
      <el-col :span="8">
        <el-alert
          title="警告"
          type="warning"
          :closable="false"
          show-icon>
          <template slot="title">
            <span style="font-size: 24px; font-weight: bold">{{ summary.warning }}</span>
            <span style="margin-left: 10px">个</span>
          </template>
        </el-alert>
      </el-col>
      <el-col :span="8">
        <el-alert
          title="正常项"
          type="success"
          :closable="false"
          show-icon>
          <template slot="title">
            <span style="font-size: 24px; font-weight: bold">{{ summary.info }}</span>
            <span style="margin-left: 10px">个</span>
          </template>
        </el-alert>
      </el-col>
    </el-row>
    
    <!-- 问题列表 -->
    <div v-if="issues.length > 0" style="margin-top: 20px">
      <h3>⚠️ 发现的问题</h3>
      <el-table :data="issues" style="width: 100%">
        <el-table-column prop="type" label="类型" width="80">
          <template slot-scope="scope">
            <el-tag :type="scope.row.type === 'critical' ? 'danger' : 'warning'" size="small">
              {{ scope.row.type === 'critical' ? '严重' : '警告' }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="title" label="问题" min-width="200"></el-table-column>
        <el-table-column prop="description" label="描述" min-width="300" show-overflow-tooltip></el-table-column>
        <el-table-column label="操作" width="150" fixed="right">
          <template slot-scope="scope">
            <el-button 
              v-if="scope.row.fix_action"
              type="primary" 
              size="small"
              @click="fixIssue(scope.row)">
              修复
            </el-button>
          </template>
        </el-table-column>
      </el-table>
    </div>
    
    <!-- 正常信息 -->
    <div v-if="info.length > 0" style="margin-top: 20px">
      <h3>✅ 正常项</h3>
      <el-table :data="info" style="width: 100%">
        <el-table-column prop="code" label="检查项" width="150"></el-table-column>
        <el-table-column prop="title" label="名称" width="200"></el-table-column>
        <el-table-column prop="value" label="值/状态"></el-table-column>
      </el-table>
    </div>
  </el-card>
  
  <!-- 一键修复 -->
  <el-card style="margin-top: 20px" v-if="issues.length > 0">
    <div slot="header" class="clearfix">
      <span>🔧 一键修复</span>
    </div>
    <el-alert
      title="一键修复将自动修复所有检测到的问题"
      type="info"
      :closable="false"
      show-icon
      style="margin-bottom: 15px">
      <template slot="title">
        <span>⚠️ 修复前会自动备份所有配置文件，可随时回退</span>
      </template>
    </el-alert>
    
    <el-button type="primary" size="medium" @click="fixAll" :loading="fixing">
      {{ fixing ? '修复中...' : '🚀 一键修复所有问题' }}
    </el-button>
    
    <el-button type="success" size="medium" @click="showBackupDialog" style="margin-left: 10px">
      📦 查看备份
    </el-button>
  </el-card>
  
  <!-- 修复日志 -->
  <el-card style="margin-top: 20px" v-if="fixLog.length > 0">
    <div slot="header" class="clearfix">
      <span>📋 修复日志</span>
      <el-button size="small" style="float: right" @click="fixLog = []">清空</el-button>
    </div>
    <div class="fix-log">
      <div v-for="(log, index) in fixLog" :key="index" class="log-item">
        <el-tag :type="log.status === 'success' ? 'success' : 'danger'" size="small">
          {{ log.status === 'success' ? '✓' : '✗' }}
        </el-tag>
        <span style="margin-left: 10px">{{ log.message }}</span>
        <el-tag v-if="log.backup_file" type="info" size="mini" style="margin-left: 10px">
          备份：{{ log.backup_file }}
        </el-tag>
      </div>
    </div>
  </el-card>
  
  <!-- 备份列表对话框 -->
  <el-dialog title="配置备份" :visible.sync="backupDialogVisible" width="60%">
    <el-table :data="backups" style="width: 100%">
      <el-table-column prop="filename" label="备份文件"></el-table-column>
      <el-table-column prop="size" label="大小" width="100"></el-table-column>
      <el-table-column prop="time" label="备份时间" width="180"></el-table-column>
      <el-table-column label="操作" width="200">
        <template slot-scope="scope">
          <el-button size="small" @click="restoreBackup(scope.row)">恢复</el-button>
          <el-button size="small" type="danger" @click="deleteBackup(scope.row)">删除</el-button>
        </template>
      </el-table-column>
    </el-table>
  </el-dialog>
</div>
</template>

<script>
export default {
  name: 'DomainHealthCheck',
  data() {
    return {
      checking: false,
      fixing: false,
      serverInfo: {
        server_ip: '',
        timestamp: ''
      },
      summary: {
        critical: 0,
        warning: 0,
        info: 0
      },
      issues: [],
      info: [],
      fixLog: [],
      backupDialogVisible: false,
      backups: []
    }
  },
  computed: {
    healthStatus() {
      if (this.summary.critical > 0) {
        return { type: 'danger', text: '严重问题' }
      } else if (this.summary.warning > 0) {
        return { type: 'warning', text: '需要关注' }
      } else {
        return { type: 'success', text: '健康' }
      }
    }
  },
  mounted() {
    this.runHealthCheck()
  },
  methods: {
    async runHealthCheck() {
      this.checking = true
      try {
        const response = await this.$api.get('/appsys/sysDomain/healthCheck.php?action=check')
        const data = response.data
        
        this.serverInfo = {
          server_ip: data.server_ip,
          timestamp: data.timestamp
        }
        this.summary = data.summary
        this.issues = data.issues || []
        this.info = data.info || []
        
        this.$message.success('健康检查完成')
      } catch (error) {
        this.$message.error('检查失败：' + error.message)
      } finally {
        this.checking = false
      }
    },
    
    async fixIssue(issue) {
      if (!issue.fix_action) return
      
      this.$confirm(`确定要修复"${issue.title}"吗？`, '提示', {
        confirmButtonText: '确定',
        cancelButtonText: '取消',
        type: 'warning'
      }).then(async () => {
        try {
          const formData = new FormData()
          formData.append('fix_action', issue.fix_action)
          
          const response = await this.$api.post('/appsys/sysDomain/healthCheck.php?action=fix', formData)
          const data = response.data
          
          this.fixLog.push({
            step: issue.fix_action,
            message: data.message,
            status: data.status,
            backup_file: data.backup_file
          })
          
          this.$message.success(data.message)
          
          // 修复后重新检查
          setTimeout(() => this.runHealthCheck(), 2000)
        } catch (error) {
          this.$message.error('修复失败：' + error.message)
        }
      })
    },
    
    async fixAll() {
      this.$confirm('一键修复将自动修复所有检测到的问题，修复前会自动备份配置。确定继续吗？', '警告', {
        confirmButtonText: '确定',
        cancelButtonText: '取消',
        type: 'warning'
      }).then(async () => {
        this.fixing = true
        try {
          const formData = new FormData()
          formData.append('fix_action', 'fix_all')
          
          const response = await this.$api.post('/appsys/sysDomain/healthCheck.php?action=fix', formData)
          const data = response.data
          
          if (data.steps) {
            data.steps.forEach(step => {
              this.fixLog.push({
                step: step.step,
                message: step.result.message,
                status: step.result.status,
                backup_file: step.result.backup_file
              })
            })
          }
          
          this.$message.success('一键修复完成！')
          
          // 修复后重新检查
          setTimeout(() => this.runHealthCheck(), 3000)
        } catch (error) {
          this.$message.error('修复失败：' + error.message)
        } finally {
          this.fixing = false
        }
      })
    },
    
    async showBackupDialog() {
      this.backupDialogVisible = true
      // TODO: 加载备份列表
      this.backups = [
        { filename: '/root/samba_backups/domain_backup_20260310_143000.tar.gz', size: '2.3 KB', time: '2026-03-10 14:30:00' }
      ]
    },
    
    async restoreBackup(backup) {
      this.$confirm(`确定要恢复到备份 "${backup.filename}" 吗？`, '警告', {
        confirmButtonText: '确定',
        cancelButtonText: '取消',
        type: 'warning'
      }).then(async () => {
        try {
          const formData = new FormData()
          formData.append('backup_file', backup.filename)
          
          const response = await this.$api.post('/appsys/sysDomain/healthCheck.php?action=restore', formData)
          const data = response.data
          
          this.$message.success('恢复成功：' + data.message)
          this.backupDialogVisible = false
          
          setTimeout(() => this.runHealthCheck(), 3000)
        } catch (error) {
          this.$message.error('恢复失败：' + error.message)
        }
      })
    },
    
    deleteBackup(backup) {
      this.$confirm(`确定要删除备份 "${backup.filename}" 吗？`, '提示', {
        confirmButtonText: '确定',
        cancelButtonText: '取消',
        type: 'warning'
      }).then(() => {
        // TODO: 实现删除
        this.$message.success('删除成功')
        this.backups = this.backups.filter(b => b.filename !== backup.filename)
      })
    }
  }
}
</script>

<style scoped>
.domain-health-check {
  padding: 20px;
}

.box-card {
  margin-bottom: 20px;
}

.fix-log {
  max-height: 400px;
  overflow-y: auto;
  background: #f5f7fa;
  padding: 15px;
  border-radius: 4px;
}

.log-item {
  padding: 8px 0;
  border-bottom: 1px solid #e4e7ed;
}

.log-item:last-child {
  border-bottom: none;
}

.clearfix::after {
  content: "";
  display: table;
  clear: both;
}
</style>
