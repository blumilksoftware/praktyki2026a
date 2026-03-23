import blumilkDefault from '@blumilksoftware/eslint-config'
import tsParser from '@typescript-eslint/parser'
import vueParser from 'vue-eslint-parser'

export default [
  ...blumilkDefault,
  {
    files: ['**/*.vue'],
    languageOptions: {
      parser: vueParser,
      parserOptions: {
        parser: tsParser,
        ecmaVersion: 'latest',
        sourceType: 'module',
      },
    },
  },
]
