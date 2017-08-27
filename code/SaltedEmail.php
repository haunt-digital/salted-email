<?php

class SaltedEmail extends Email
{
    public function send($messageID = null)
    {
        Requirements::clear();

        $this->parseVariables();

        if(empty($this->from)) $this->from = Email::config()->admin_email;

        $headers = $this->customHeaders;

        if($messageID) $headers['X-SilverStripeMessageID'] = project() . '.' . $messageID;

        if(project()) $headers['X-SilverStripeSite'] = project();


        $to = $this->to;
        $from = $this->from;
        $subject = $this->subject;
        if($sendAllTo = $this->config()->send_all_emails_to) {
            $subject .= " [addressed to $to";
            $to = $sendAllTo;
            if($this->cc) $subject .= ", cc to $this->cc";
            if($this->bcc) $subject .= ", bcc to $this->bcc";
            $subject .= ']';
            unset($headers['Cc']);
            unset($headers['Bcc']);

        } else {
            if($this->cc) $headers['Cc'] = $this->cc;
            if($this->bcc) $headers['Bcc'] = $this->bcc;
        }


        if($ccAllTo = $this->config()->cc_all_emails_to) {
            if(!empty($headers['Cc']) && trim($headers['Cc'])) {
                $headers['Cc'] .= ', ' . $ccAllTo;
            } else {
                $headers['Cc'] = $ccAllTo;
            }
        }

        if($bccAllTo = $this->config()->bcc_all_emails_to) {
            if(!empty($headers['Bcc']) && trim($headers['Bcc'])) {
                $headers['Bcc'] .= ', ' . $bccAllTo;
            } else {
                $headers['Bcc'] = $bccAllTo;
            }
        }

        if($sendAllfrom = $this->config()->send_all_emails_from) {
            if($from) $subject .= " [from $from]";
            $from = $sendAllfrom;
        }

        Requirements::restore();

        $css_path   =   realpath(Director::baseFolder() . Config::inst()->get('Email', 'CSSPath'));

        $mergedHtml =   $this->body;
        if (file_exists($css_path)) {
            $emogrifier = new \Pelago\Emogrifier();

            $html       =   $this->body;
            $css        =   file_get_contents($css_path);

            $emogrifier->setHtml($html);
            $emogrifier->setCss($css);
            $emogrifier->disableStyleBlocksParsing();

            $mergedHtml = $emogrifier->emogrify();
        }

        // Debugger::inspect($mergedHtml);

        return self::mailer()->sendHTML($to, $from, $subject, $mergedHtml, $this->attachments, $headers, $this->plaintext_body);
    }
}
