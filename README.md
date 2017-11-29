# yii2_redis_mailerqueue
yii2 redis async mailer

基于 Redis的邮件异步发送工具

1.config.php
...

'aliases' => [
		...
        '@jsoma/mailer'=>'@vendor/jsoma/mailerqueue/src',
        ...
    ],
 ...

  'components' => [
  		...
        'redis' => [
            'class' => 'yii\redis\Connection',
            'hostname' => 'localhost', // ip/127.0.0.1
            'port' => 6379,
            'database' => 0,
        ],

       ...

        'mailer' => [

            //原始发送邮件
//            'class' => 'yii\swiftmailer\Mailer',
//            // send all mails to a file by default. You have to set
//            // 'useFileTransport' to false and configure a transport
//            // for the mailer to send real emails.
//            'useFileTransport' => false,
//            'transport' => [
//                'class' => 'Swift_SmtpTransport',
//                'host' => 'smtp.163.com',
//                'username' => 'xxx@163.com',
//                'password' => 'xxx',
//                'port' => '465',
//                'encryption' => 'ssl',
//            ],

            //redis 发送邮件
             'class' => 'jsoma\mailer\MailerQueue',
             'db' => '1', // redis select 1
             'key' => 'mails', // redis key
            'useFileTransport' => false,
            'transport' => [
                'class' => 'Swift_SmtpTransport',
                'host' => 'smtp.163.com',
                'username' => 'xxx@163.com',
                'password' => 'xxx',
                'port' => '465',
                'encryption' => 'ssl',
            ],

        ], 

    ],

    . . . .



2.  mail to redis
	......
	$mailer = Yii::$app->mailer->compose($template, $param=[]);//邮件体
    $mailer->setFrom('xxx@163.com'); //发件人
    $mailer->setTo('xxx@xxx.com'); // 收件人
    $mailer->setSubject('xxx'); // 邮件标题
    //if ($mailer->send()) { // 
    if ($mailer->queue()) { // redis 发送
        return true;
    }else{
		return false;
	}
	....
3. Send mail by redis ( linux crontab 定时脚本)

	...
	$mailer = new \jsoma\mailer\MailerQueue();
    $mailer->process();
    ...

	


