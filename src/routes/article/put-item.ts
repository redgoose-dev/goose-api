import DB, { db } from '@/classes/DB'
import ServiceError from '@/classes/ServiceError'
import { parseJSON } from '@/libs/objects'
import * as helper from './__helper'

export default async function putItem()
{
  try
  {
    // get ready data
    let article = db.getData({
      table: DB.TABLE.ARTICLE,
      where: `mode LIKE \'${helper.STATUS.READY}\'`
    })

    // 대기 데이터가 없으면 데이터를 만들고 가져온다.
    if (!article.data)
    {
      const newArticle = db.addData({
        table: DB.TABLE.ARTICLE,
        values: [
          { key: 'mode', value: helper.STATUS.READY },
        ],
      })
      article = db.getData({
        table: DB.TABLE.ARTICLE,
        where: `srl = ${newArticle.data}`,
      })
    }

    // filtering article data
    if (article.data?.json)
    {
      article.data.json = parseJSON(article.data.json)
    }

    return article.data
  }
  catch (_e: any)
  {
    throw new ServiceError('Failed to add Article.', {
      status: _e.status,
      text: _e.message,
      cause: _e,
    })
  }
}
