<?php

namespace ModulesGarden\TTSGGSModule\Components\TicketReplyPreview;

use ModulesGarden\TTSGGSModule\Components\Container\Container;
use ModulesGarden\TTSGGSModule\Components\Image\Image;
use ModulesGarden\TTSGGSModule\Core\Components\Traits\ActionOnChangeTrait;
use ModulesGarden\TTSGGSModule\Core\WHMCS\URL\Admin;

class TicketReplyPreview extends Container
{
    use ActionOnChangeTrait;

    public const COMPONENT = 'TicketReplyPreview';
    protected $item;
    protected string $storagePath = '';

    public function __construct($ticketReply)
    {
        $this->item = $ticketReply;
        $this->build();
        parent::__construct();

        $this->setTranslations([
            'date',
            'download_attachment',
            'delete_attachment',
            'delete_attachment_message',
        ]);
    }

    public function addEditButton($button): self
    {
        $this->addComponent('buttons', $button);
        return $this;
    }

    public function setStoragePath(string $storagePath): void
    {
        $this->storagePath = $storagePath;
    }

    protected function build()
    {
        $this->buildReplyInfo();
        $this->buildUserData();
        $this->buildMessage();
        $this->buildAttachments();
        $this->buildStorageAttachments();
    }

    protected function buildReplyInfo()
    {
        $this->setSlot('replyId', $this->item->id);
        $this->setSlot('posted', $this->item->date);
    }

    protected function buildMessage()
    {
        $markup = new \WHMCS\View\Markup\Markup();
        $this->setSlot('message', html_entity_decode($markup->transform($this->item->message, $this->item->editor)));
    }

    protected function buildUserData()
    {
        $image = new Image();

        if ($this->item->userid) {
            $this->setSlot('userName', $this->item->client->firstname . " " . $this->item->client->lastname);
            $image->setUrl($this->getGravatar($this->item->user->email));
        } else {
            $this->setSlot('userName', $this->item->admin);
            $image->setUrl($this->getGravatar(""));
        }

        $this->setSlot('avatarImage', $image);
    }

    protected function buildAttachments()
    {
        if (empty($this->item->attachment)) {
            return;
        }

        $attachmentNames = explode('|', $this->item->attachment);
        $attachment      = [];
        $index = 0;

        foreach ($attachmentNames as $attachmentName) {
            $attachment['name']        = $attachmentName;
            $attachment['downloadUrl'] = Admin::downloadAttachmentUrl($this->item->title ? 'a' : 'ar', $this->item->id, $index) ;
            $attachment['showUrl']     = Admin::showAttachmentUrl($this->item->title ? 't' : 'r', $this->item->id, $index);
            $attachment['deleteUrl']   = Admin::deleteAttachmentUrl($this->item->id, $index, $this->item->title ? 0 : $this->item->tid, $this->item->title ? "" : 'r');

            $index++;

            $this->pushToSlot('attachments', $attachment);
        }
    }

    public static function getGravatar($email, $s = 60, $d = 'mp', $r = 'g', $img = false, array $atts = [])
    {
        $url = 'https://www.gravatar.com/avatar/';
        $url .= md5(strtolower(trim($email)));
        $url .= "?s=$s&d=$d&r=$r";
        if ($img) {
            $url = '<img src="' . $url . '"';
            foreach ($atts as $key => $val) {
                $url .= ' ' . $key . '="' . $val . '"';
            }
            $url .= ' />';
        }
        return $url;
    }

    public function buildStorageAttachments()
    {
        if (empty($this->item->attachments)) {
            return;
        }

        $storageAttachments = is_array($this->item->attachments) ?
            $this->item->attachments : json_decode($this->item->attachments);

        $this->setSlot('storagePath', $this->storagePath);
        $this->setSlot('storageAttachments', $storageAttachments);
    }
}