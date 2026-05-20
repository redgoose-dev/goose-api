/**
 * [GET] /
 *
 * Home
 */

export default async (ctx: Context) => {

  const { service } = ctx.store

  return {
    message: `Hello! ${service.serviceName}`,
    foo: 'bar',
  }

}
