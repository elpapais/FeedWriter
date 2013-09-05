<?php
namespace FeedWriter\View;

use FeedWriter\View\SyndicationBase;

/*
 * Copyright (C) 2012 Michael Bemmerl <mail@mx-server.de>
 *
 * This file is part of the "Universal Feed Writer" project.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * Wrapper for creating ATOM feeds
 *
 * @package     UniversalFeedWriter
 */
class Atom extends SyndicationBase
{
  public $require = [
    'date',
    'author',
    'content',
    'title',
    'link',
  ];

	/**
	* {@inheritdoc}
	*/
	function __construct()
	{
		parent::__construct(SyndicationBase::ATOM);
	}

  public function collect ($source_item)
  {
    $item = $this->createNewItem();

    $item->setTitle($source_item->title);
    $item->setLink($source_item->link);
    $item->setDate($source_item->date);
    $item->setAuthor($source_item->author);

    // TODO:  Hrm.
    // $item->setEnclosure('http://upload.wikimedia.org/wikipedia/commons/4/49/En-us-hello-1.ogg', 11779, 'audio/ogg');

    // Internally changed to "summary" tag for ATOM feed:
    $item->setDescription('This is a test of adding CDATA encoded description by the php <b>Universal Feed Writer</b> class');
    $item->setContent($source_item->content);

    $this->addItem($item);
  }

}
