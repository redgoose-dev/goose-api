/**
 * Service || App
 */

export abstract class App {

  static async getIndex()
  {
    return {
      message: `GET /app/`,
    }
  }

  static async getItem(srl: number)
  {
    return {
      message: `GET /app/:srl/`,
      path: `GET /app/${srl}/`,
    }
  }

  static async putItem()
  {
    return {
      message: `PUT /app/`,
    }
  }

  static async patchItem()
  {
    return {
      message: `PATCH /app/:srl/`,
    }
  }

  static async deleteItem()
  {
    return {
      message: `DELETE /app/:srl/`,
    }
  }

}
