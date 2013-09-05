<?php
namespace FeedWriter;

use \Iterator;
use \Exception;
use \stdClass;
use \ArrayIterator;

class Feed {

	const RSS1 = 'RSS 1.0';
	const RSS2 = 'RSS 2.0';
	const ATOM = 'ATOM';

  protected $_iter  = null;
  protected $_map   = [];
  protected $_views = [];

  /**
   * Generate one or more views of a collection from a given iterator or array.
   *
   * Takes a data source, an array describing a mapping from instances of the
   * collection to items in the view, and a list of views to generate.
   *
   * This all sounds more abstract than it actually is, so let's run through
   * some examples:
   *
   * <code>
   *
   * use \FeedWriter\Feed;
   * use \FeedWriter\View\Atom;
   *
   * // Here's an array of arrays describing some blog posts.  These could also
   * // be objects with properties like $blog_posts->writer.
   *
   * $blog_posts = [
   *   [ 'date_created' => '2013-02-14', 'writer' => 'Brennen Bearnes', 'text' => 'Hate.' ],
   *   [ 'date_created' => '2013-07-04', 'writer' => 'Brennen Bearnes', 'text' => 'Explosions.' ],
   *   [ 'date_created' => '2013-08-31', 'writer' => 'Brennen Bearnes', 'text' => 'A feed thingy.' ],
   * ];
   *
   * // Here's a map that explains how to find the properties we need for our
   * // view:
   * $entry_from_post = [
   *   'date'        => 'date_created',
   *   'author'      => 'writer',
   *   'content'     => 'text'
   * ];
   *
   * $feed = new Feed($blog_posts, $entry_from_post, ['atom' => new Atom]);
   *
   * // If everything went ok, views are now available as properties of the feed.
   * if (! $feed->error())
   *   echo $feed->atom->render();
   * else
   *   echo 'error: ' . $feed->error();
   *
   * </code>
   *
   * @param $source mixed iterator or array
   * @param $map array describing mapping of fields to fields
   * @param $views array one or more views to generate
   */
  public function __construct ($source, array $map, array $views)
  {
    if (is_array($source)) 
      $iter = new ArrayIterator($source);
    else
      $iter = $source;

    $this->setIter($iter);
    $this->setViewsAndMap($views, $map);
    $this->spin();
  }

  /**
   * Explode if anybody tries to set a property directly.
   */
  public function __set ($name, $view)
  {
    throw new Exception("You can't directly set properties on Feed. See documentation for constructor.");
  }

  /**
   * Get a view.
   */
  public function __get ($name)
  {
    return $this->_views[$name];
  }

  /**
   * Is a given view set?
   */
  public function __isset ($name)
  {
    return isset($this->_views[$name]);
  }

  /**
   * Stash views and map, making sure that the map provides for anything
   * the view explicitly requires.
   */
  protected function setViewsAndMap (array $views, array $map)
  {
    if (! count($views))
      throw new Exception('Feed requires that at least one view instance be passed in)');

    if (! is_array($map))
      throw new Exception('$map must be an array');

    if (! count($map))
      throw new Exception('$map must provide values');

    // Validate map for each view:
    foreach ($views as $name => $view) {
      if (! isset($view->require))
        continue;

      foreach ($view->require as $field) {
        if (! isset($map[ $field ])) {
          throw new Exception('$map should provide a mapping for ' . $field);
        }
      }
    }

    $this->_views = $views;
    $this->_map   = $map;
  }

  protected function setIter (Iterator $iter)
  {
    if (! ($iter instanceof Iterator))
      throw new Exception('$iter must be an iterator');
    $this->_iter = $iter;
  }

  /**
   * Do the business of handing off values to views.
   */
  protected function spin ()
  {
    $output_item = (object)[];
    while ($this->_iter->valid()) {
      $input_item = $this->_iter->current();

      if (is_array($input_item))
        $input_item = (object)$input_item;

      foreach ($this->_map as $output_key => &$input_key) {
        if (is_string($input_key))
        {
          // shorthand for "use this field on the object for the value"
          if (isset($input_item->$input_key))
            $output_item->$output_key = $input_item->$input_key;
          else
            throw new Exception("unable to access $input_key on input item: " . print_r($input_item, 1));
        }
        elseif (is_callable($input_key))
        {
          // if we have a function, pass it the item and expect it to
          // return a value for our output key
          $output_item->$output_key = $input_key($input_item);
        }
      }

      foreach ($this->_views as $name => $view) {
        $view->collect($output_item);
      }

      $this->_iter->next();
    }
  }

}
