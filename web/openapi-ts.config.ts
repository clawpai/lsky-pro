import { defineConfig } from '@hey-api/openapi-ts';

export default defineConfig({
  input: 'http://127.0.0.1:4523/export/openapi/2?version=3.0',
  output: 'src/api',
  plugins: ['@hey-api/client-axios'],
});
