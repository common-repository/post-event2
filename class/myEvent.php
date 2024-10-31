<?php
/*
Version: 3.0
Author: oXfoZ
Author URI: http://www.oxfoz.com/
*/

class MyEvent
{
	private $id;
	private $postId;
	private $place;
	private $dateStart;
	private $dateEnd;
	private $timeStart;
	private $timeEnd;
	private	$subscribe;
	private	$guests;
	private	$parts;
	private	$shownb;
	
	/*
	** Class consructor
	*/
	public function	MyEvent($postId, $dateStart, $place)
	{
		$this->setPostId($postId);
		$this->setDateStart($dateStart);
		$this->setPlace($place);
	}
	
	/*
	** Get the event id
	*/
	public function	getId()
	{
		return $this->id;
	}
	
	/*
	** Get the post id
	*/
	public function	getPostId()
	{
		return $this->postId;
	}
	
	/*
	** Get the event place
	*/
	public function	getPlace()
	{
		return $this->place;
	}
	
	/*
	** Get the event date start
	*/
	public function	getDateStart()
	{
		return $this->dateStart;
	}
	
	/*
	** Get the event date end
	*/
	public function	getDateEnd()
	{
		return $this->dateEnd;
	}
	
	/*
	** Get the event time start
	*/
	public function	getTimeStart()
	{
		return $this->timeStart;
	}
	
	/*
	** Get the event subscribe option
	*/
	public function	getSubscribe()
	{
		return $this->subscribe;
	}
	
	/*
	** Get the event time end
	*/
	public function	getTimeEnd()
	{
		return $this->timeEnd;
	}
	
	/*
	** Set the event id
	*/
	public function	setId($id)	
	{
		$this->id = $id;
	}
	
	/*
	** Set the post id
	*/
	public function	setPostId($postid)
	{
		$this->postId = $postid;
	}
	
	/*
	** Set the event place
	*/
	public function	setPlace($place)
	{
		$this->place = $place;
	}
	
	/*
	** Set the event date start
	*/
	public function	setDateStart($dateStart)
	{
		$this->dateStart = $dateStart;
	}
	
	/*
	** Set the event date end
	*/
	public function	setDateEnd($dateEnd)
	{
		$this->dateEnd = $dateEnd;
	}
	
	/*
	** Set the event time start
	*/
	public function	setTimeStart($hour)
	{
		$this->timeStart = $hour;
	}
	
	/*
	** Set the event time end
	*/
	public function	setTimeEnd($hour)
	{
		$this->timeEnd = $hour;
	}
	
	/*
	** Set the event subscribe option
	*/
	public function	setSubscribe($value)
	{
		$this->subscribe = $value;
	}
	
	/*
	** Set the event nb guests
	*/
	public function	setGuests($value)
	{
		$this->guests = $value;
	}
	
	/*
	** Get the event nb guests
	*/
	public function	getGuests()
	{
		return $this->guests;
	}
	
	/*
	** Set the event max participant
	*/
	public function	setParts($value2)
	{
		$this->parts = $value2;
	}
	
	/*
	** Get the event max participant
	*/
	public function	getParts()
	{
		return $this->parts;
	}
	
	/*
	** Set the event visibility option
	*/
	public function	setShow($value)
	{
		$this->shownb = $value;
	}
	
	/*
	** Get the event visibility option
	*/
	public function	getShow()
	{
		return $this->shownb;
	}
}
