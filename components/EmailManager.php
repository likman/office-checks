<?php
namespace app\components;

use Yii;
use function count;

class EmailManager
{
    private $_subject;
    private $_to;
    private $_body;
    private $_from;
    private $_cc;
    private $_reply_to;
    private $_message;

    public static function build()
    {
        return (new self());
    }

    public function __construct() {
        Yii::$app->mailer->init();
        $this->_message = Yii::$app->mailer->compose();
    }

    public function setSubject($value, $add_site_name=true) {
        if ($add_site_name) {
            $value=Yii::$app->id.'. '.$value;
        }
        $this->_message->setSubject($value);
        $this->_subject=$value;
        return $this;
    }

    public function setTo($value) {
        if (is_array($value)) {
            $emails=$value;
            foreach ($emails as &$email) {
                $email=self::getFirstEmail($email);
            }
            unset($email);
        } else {
            $emails = self::convertEmailsToArray($value);
        }
        foreach ($emails as $key=>$email) {
            if (!Helper::isOk(trim($email))) {
                unset($emails[$key]);
            }
        }
        if (count($emails)==0) {
            return $this;
        }
        $this->_message->setTo($emails);
        $this->_to=$emails;
        return $this;
    }

    public function setTextBody($value) {
        $this->_message->setTextBody($value);
        $this->_body=$value;
        return $this;
    }

    public function setHtmlBody($value) {
        $body=str_replace("\r\n",'<br>',$value);
        $this->_message->setHtmlBody($body);
        $this->_body=$body;
        return $this;
    }

    public function setFrom($value) {
        if (!is_array($value)) {
            $from=self::getFirstEmail($value);
        } else {
            $from=$value;
        }
        if (!Helper::isOk($from)) {
            return $this;
        }
        $this->_message->setFrom($from);
        $this->_from=$from;
        return $this;
    }

    public function setCc($value) {
        if (is_array($value)) {
            $emails=$value;
            foreach ($emails as &$email) {
                $email=self::getFirstEmail($email);
            }
            unset($email);
        } else {
            $emails = self::convertEmailsToArray($value);
        }
        foreach ($emails as $key=>$email) {
            if (!Helper::isOk(trim($email))) {
                unset($emails[$key]);
            }
        }
        if (count($emails)==0) {
            return $this;
        }
        $this->_message->setCc($emails);
        $this->_cc=$emails;
        return $this;
    }

    public function setReplyTo($value) {
        if (is_array($value)) {
            $email=$value[0];
        } else {
            $email = self::getFirstEmail($value);
        }
        if (!Helper::isOk($email)) {
            return $this;
        }
        $this->_message->setReplyTo($email);
        $this->_reply_to=$email;
        return $this;
    }

    public function attach($file, $params=[])
    {
        $this->_message->attach($file,$params);
        return $this;
    }

    public function attachContent($content, $params=[])
    {
        $this->_message->attachContent($content,$params);
        return $this;
    }

    public function send()
    {
        if (!isset($this->_to) || ((is_array($this->_to) && count($this->_to)==0)) || !Helper::isOk($this->_to)) {
            return;
        }
        if (!isset($this->_from)) {
            $this->setFrom(Yii::$app->params['robotEmail']);
        }
        $unsubscribe_link=Yii::$app->params['address'].'site/unsubscribeemail';
        //  $this->_message->getSwiftMessage()->getHeaders()->addTextHeader('List-Unsubscribe', '<'.$unsubscribe_link.'>');
        $this->_message->mailer->adapter->addCustomHeader("List-Unsubscribe",'<'.$unsubscribe_link.'>');
        $this->_message->setCharset('UTF-8');
        $this->_message->send();
    }

    private function removeUnsubscribedFromEmails() {
        $new_email_array=[];
        if (is_array($this->_to)) {
            foreach ($this->_to as $email) {
                if (!$this->isEmailUnsubscribed($email)) {
                    $new_email_array[]=$email;
                }
            }
        } else {
            if (!$this->isEmailUnsubscribed($this->_to)) {
                $new_email_array[]=$this->_to;
            }
        }
        $this->_to=$new_email_array;
    }

    private function isEmailUnsubscribed($email) {
        if (!Helper::isOk($email)) {
            return false;
        }
        $sql='select id from r_email_unsubscribe_list 
              where email=:email';
        $params=[
            ':email'=>$email,
        ];
        $rows=Yii::$app->db->createCommand($sql)->bindValues($params)->cache(30)->queryAll();
        if (count($rows)>0) {
            return true;
        }
        return false;
    }

    public static function convertEmailsToArray($emails)
    {
        $array=[];
        $emails=str_replace(';', ',', $emails);
        while (($pos=mb_strpos($emails, ','))!==false) {
            $email=trim(mb_substr($emails, 0, $pos));
            $array[]=$email;
            $emails=mb_substr($emails, $pos+1);
        }
        if (mb_strpos($emails, '@')!==false) {
            $array[]=trim($emails);
        }
        return $array;
    }
    public static function getFirstEmail($emails)
    {
        $emails=str_replace(';', ',', $emails);
        $pos=mb_strpos($emails, ',');
        if ($pos===false) {
            return trim($emails);
        }
        return trim(mb_substr($emails, 0, $pos));
    }

}
