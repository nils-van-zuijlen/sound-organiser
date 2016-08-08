<?php

namespace Xif\UserBundle\FormModels;

use Xif\UserBundle\Entity\User;

class Mail {
	protected $to;
	protected $toName;
	protected $mailContent;
	protected $subject;

	public function setMailContent($mailContent)
	{
		$this->mailContent = (string) $mailContent;
		return $this;
	}
	public function getMailContent()
	{
		return $this->mailContent;
	}

	public function setSubject($subject)
	{
		$this->subject = (string) $subject;
		return $this;
	}
	public function getSubject()
	{
		return $this->subject;
	}

	public function __construct(User $user = null)
	{
		$this->to = $user->getEmail();
		$this->toName = $user->getUsername();
	}

	public function done(\Swift_Message $mail)
	{
		$mail->setSubject($this->subject.' – SoundOrganiser');
		$mail->setTo($this->to, $this->toName);
		return $this->mailContent;
	}
}