const { NODE_ENV } = Bun.env

export const IS_DEV = NODE_ENV !== 'production'
