import DB, { db } from '@/classes/DB'
import ServiceError from '@/classes/ServiceError'
import { createCode } from '@/libs/strings'
import { deleteFile, getUploadPath } from '@/libs/file'
import { parseJSON, isObject, filteringObject } from '@/libs/objects'
import * as messages from '@/libs/messages'
import * as helper from './__helper'
import type Service from '@/classes/Service'
import type { FileModel } from './__model'
import type { FileToResource } from './__helper'

export default async function deleteItem(srl: number)
{
  try
  {
    console.log('File_.deleteItem()', srl)
  }
  catch (_e: any)
  {
    throw new ServiceError('Failed to delete File.', {
      status: _e.status,
      text: _e.message,
      cause: _e,
    })
  }
}
