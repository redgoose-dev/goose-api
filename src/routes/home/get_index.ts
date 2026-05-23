/**
 * [GET] /
 *
 * Home
 */

export default async (ctx: Context) => {

  const { request, store } = ctx

  // store.logger.info(request, 'Hello, World!', {
  //   now: Date.now(),
  // })

  return {
    message: `Hello! ${store.service.serviceName}`,
    foo: 'bar',
  }

}
