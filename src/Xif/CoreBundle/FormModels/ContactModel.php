<?php

namespace Xif\CoreBundle\FormModels;

use Symfony\Component\Validator\Constraints as Assert;

class ContactModel
{
	/**
	 * @Assert\Length(max=255, min=3, maxMessage="Doit faire au plus {{ limit }} caractères", minMessage="Doit faire au moins {{ limit }} caractères")
	 */
	protected $subject;

	/**
	 * @Assert\Length(min=10, minMessage="Doit faire au moins {{ limit }} caractères")
	 */
	protected $body;

	/**
	 * @Assert\Email(checkMX=true, message="Veuillez entrer une adresse e-mail correcte")
	 */
	protected $senderMail;

	/**
	 * @Assert\Length(min=3, minMessage="Doit faire au moins {{ limit }} caractères")
	 */
	protected $senderName;

	public function getSubject()
	{
		return $this->subject;
	}
	public function setSubject($subject)
	{
		$this->subject = $subject;
		return $this;
	}

	public function getBody()
	{
		return $this->body;
	}
	public function setBody($body)
	{
		$this->body = $body;
		return $this;
	}

	public function getSenderMail()
	{
		return $this->senderMail;
	}
	public function setSenderMail($senderMail)
	{
		$this->senderMail = $senderMail;
		return $this;
	}

	public function getSenderName()
	{
		return $this->senderName;
	}
	public function setSenderName($senderName)
	{
		$this->senderName = $senderName;
		return $this;
	}

	public function done($mail)
	{
		$mail->setSubject('Contact – '.$this->subject);
		$mail->setReplyTo($this->senderMail, $this->senderName);
		$mail->setFrom($this->senderMail);
		$mail->setBody('Envoyé par '.$this->senderName.' ( '.$this->senderMail.' )

'.$this->body, 'text/plain');
	}
}
