/**
 * Service || Home
 */

export abstract class Home {

  static getInfo(service: Service)
  {
    return {
      message: `Hello! ${service.serviceName}`,
      version: service.version,
      dev: service.dev,
    }
  }

}
