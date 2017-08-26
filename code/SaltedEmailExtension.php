<?php

class SaltedEmailExtension extends DataExtension {
    public function send($messageID = null)
    {
        Requirements::clear();

        $this->owner->parseVariables();

        if(empty($this->owner->from)) $this->owner->from = Email::config()->admin_email;

        $headers = $this->owner->customHeaders;

        if($messageID) $headers['X-SilverStripeMessageID'] = project() . '.' . $messageID;

        if(project()) $headers['X-SilverStripeSite'] = project();


        $to = $this->owner->to;
        $from = $this->owner->from;
        $subject = $this->owner->subject;
        if($sendAllTo = $this->owner->config()->send_all_emails_to) {
            $subject .= " [addressed to $to";
            $to = $sendAllTo;
            if($this->owner->cc) $subject .= ", cc to $this->owner->cc";
            if($this->owner->bcc) $subject .= ", bcc to $this->owner->bcc";
            $subject .= ']';
            unset($headers['Cc']);
            unset($headers['Bcc']);

        } else {
            if($this->owner->cc) $headers['Cc'] = $this->owner->cc;
            if($this->owner->bcc) $headers['Bcc'] = $this->owner->bcc;
        }


        if($ccAllTo = $this->owner->config()->cc_all_emails_to) {
            if(!empty($headers['Cc']) && trim($headers['Cc'])) {
                $headers['Cc'] .= ', ' . $ccAllTo;
            } else {
                $headers['Cc'] = $ccAllTo;
            }
        }

        if($bccAllTo = $this->owner->config()->bcc_all_emails_to) {
            if(!empty($headers['Bcc']) && trim($headers['Bcc'])) {
                $headers['Bcc'] .= ', ' . $bccAllTo;
            } else {
                $headers['Bcc'] = $bccAllTo;
            }
        }

        if($sendAllfrom = $this->owner->config()->send_all_emails_from) {
            if($from) $subject .= " [from $from]";
            $from = $sendAllfrom;
        }

        Requirements::restore();
        $css_path   =   realpath(Director::baseFolder() . Config::inst()->get('Email', 'CSSPath'));
        $mergedHtml =   $this->owner->body;
        if (file_exists($css_path)) {

            $emogrifier = new \Pelago\Emogrifier();

            $html       =   $this->owner->body;
            $css        =   file_get_contents($css_path);

            $emogrifier->setHtml($html);
            $emogrifier->setCss($css);
            $emogrifier->disableStyleBlocksParsing();

            $mergedHtml = $emogrifier->emogrify();
        }

        // Debugger::inspect($mergedHtml);

        return self::mailer()->sendHTML($to, $from, $subject, $mergedHtml, $this->owner->attachments, $headers,
            $this->owner->plaintext_body);
    }
}
