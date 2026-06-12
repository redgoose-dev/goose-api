const { SERVICE_NAME } = Bun.env

// Print start
console.log(`🪴 Start build ${SERVICE_NAME}...`)

// Run build
await Bun.build({
  root: '.',
  entrypoints: [
    './src/main.ts',
    './src/util.ts',
  ],
  outdir: './dist',
  target: 'bun',
  format: 'esm',
  splitting: true,
  minify: {
    whitespace: true,
    identifiers: true,
    syntax: true,
  },
  external: [
    'bcryptjs',
    'elysia',
    'jsonwebtoken',
    'logixlysia',
  ],
  naming: {
    entry: '[name].[ext]',
    chunk: 'chunk/[name]-[hash].[ext]',
    asset: 'asset/[name]-[hash].[ext]',
  },
  define: {
    'Bun.env.USE_BUILD': JSON.stringify(true),
  },
  plugins: [],
})

// Print complete
console.log(`✅ Complete build ${SERVICE_NAME}...`)
