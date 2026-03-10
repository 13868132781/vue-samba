document.addEventListener("DOMContentLoaded", function () {
    document.querySelectorAll("input[data-col='mfa']").forEach(function (el) {
        el.addEventListener("change", function () {
            const id = this.getAttribute("data-id");
            const val = this.checked ? "1" : "0";

            if (!confirm("确定修改？")) {
                this.checked = !this.checked;
                return;
            }
 
            fetch("/sdLdap/adUser@editSaveBefore_modMfa", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({
                    key: id,
                    formVal: { mfa: val }
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.code === 0) {
                    alert("保存成功");
                    location.reload();
                } else {
                    alert("保存失败: " + data.msg);
                    this.checked = !this.checked;
                }
            })
            .catch(err => {
                console.error(err);
                alert("网络错误，请检查控制台");
                this.checked = !this.checked;
            });
        });
    });
});