module.exports = {
  preset: '@vue/cli-plugin-unit-jest',
  testMatch: [
    '**/tests/**/*.spec.js',
    '**/tests/**/*.test.js'
  ],
  transform: {
    '^.+\\.vue$': '@vue/vue3-jest',
    '^.+\\.js$': 'babel-jest',
    '^.+\\.jsx?$': 'babel-jest'
  },
  transformIgnorePatterns: [
    '/node_modules/(?!(@vue|axios|vue-router))'
  ],
  moduleNameMapper: {
    '^@/(.*)$': '<rootDir>/$1',
    '^~/(.*)$': '<rootDir>/$1',
    '\\.(css|less|scss|sass)$': 'identity-obj-proxy'
  },
  moduleFileExtensions: ['js', 'jsx', 'json', 'vue'],
  testEnvironment: 'jsdom',
  testEnvironmentOptions: {
    url: 'http://localhost/'
  },
  collectCoverageFrom: [
    'coms/**/*.js',
    'index/**/*.vue',
    'api/**/*.js',
    '!**/node_modules/**',
    '!**/vendor/**',
    '!**/tests/**',
    '!**/coverage/**'
  ],
  coverageReporters: ['html', 'text', 'lcov', 'clover'],
  coverageDirectory: '<rootDir>/tests/coverage',
  verbose: true,
  bail: false,
  testURL: 'http://localhost/',
  setupFilesAfterEnv: ['<rootDir>/tests/setup.js'],
  snapshotSerializers: ['jest-serializer-vue']
};
