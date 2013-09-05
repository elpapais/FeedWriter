<?php
namespace FeedWriter\View;

use \SparkLib\CSV as SparkCSV;

class CSV {

  public function __construct ()
  {
    $this->_csv = new SparkCSV();
    $this->_csv->addHeader(['date', 'author']);
  }

  public function collect ($item)
  {
    $this->_csv->addRow([$item->date, $item->author]);
  }

  public function generateFeed ()
  {
    return $this->_csv->render();
  }

}
