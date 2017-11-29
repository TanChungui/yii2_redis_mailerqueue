<?php
namespace jsoma;

use Yii;

class Message extends \yii\swiftmailer\Message
{
    public function queue()
    {
        $redis = Yii::$app->redis;
        if (empty($redis)) {
            throw new \yii\base\InvalidConfigException('redis not found in config.');
        }
        // 0 - 15  select 0 select 1
        // db => 1
        $mailer = Yii::$app->mailer;
        if (empty($mailer) || !$redis->select($mailer->db)) {
            throw new \yii\base\InvalidConfigException('db not defined.');
        }

        $message = [];
        $message['from'] = $this->getFrom(); //发送者 email
        $message['to'] = array_keys($this->getTo()); // 接收者 email

        if($this->getCc()){
            $message['cc'] = array_keys($this->getCc()); // 抄送
        }
        if($this->getBcc()){
            $message['bcc'] = array_keys($this->getBcc()); //密件抄送
        }

        if($this->getReplyTo()){
            $message['reply_to'] = $this->getReplyTo(); // 恢复地址
        }

        if($this->getCharset()){
            $message['charset'] = $this->getCharset(); //编码设置
        }

        if($this->getSubject()){
            $message['subject'] = $this->getSubject(); //标题
        }
        $parts = $this->getSwiftMessage()->getChildren();
        if (!is_array($parts) || !sizeof($parts)) {
            $parts = [$this->getSwiftMessage()];
        }
        foreach ($parts as $part) {
            if (!$part instanceof \Swift_Mime_Attachment) {
                switch($part->getContentType()) {
                    case 'text/html':
                        $message['html_body'] = $part->getBody();
                        break;
                    case 'text/plain':
                        $message['text_body'] = $part->getBody();
                        break;
                }
                if (!$message['charset']) {
                    $message['charset'] = $part->getCharset();
                }
            }
        }
        return $redis->rpush($mailer->key, json_encode($message));
    }
}
