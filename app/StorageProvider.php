<?php

namespace App;

/**
 * 存储类型枚举
 */
enum StorageProvider: string
{
    /** 本地 */
    case Local = 'local';

    /** AWS S3 */
    case S3 = 's3';

    /** 阿里云 Oss */
    case Oss = 'oss';

    /** 腾讯云 Cos */
    case Cos = 'cos';

    /** 七牛云 Kodo */
    case Qiniu = 'qiniu';

    /** 又拍云 Uss */
    case Upyun = 'upyun';

    /** Sftp */
    case Sftp = 'sftp';

    /** Ftp */
    case Ftp = 'ftp';

    /** Webdav */
    case Webdav = 'webdav';
}
