<?php
namespace FeedWriter\View;

class JSON {

  public $require = ['date', 'author'];

  protected $_struct = [];

  public function collect ($item)
  {
    $this->_struct[] = [
      'date'   => $item->date,
      'author' => $item->author
    ];
  }

  public function generateFeed ()
  {
    return json_encode($this->_struct);
  }

}
