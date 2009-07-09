<?php
// $Id$

/**
 * @file
 * Provides the Disqus PHP API.
 */

/**
 * The Disqus PHP API.
 *
 * @package    Disqus
 * @author     Rob Loach (http://www.robloach.net)
 * @version    2.1
 * @access     public
 * @license    GPL (http://www.opensource.org/licenses/gpl-2.0.php)
 *
 * @example
 * @code
 *   // The user API key obtained from http://disqus.com/api/get_my_key/ .
 *   $user_api_key = 'Your Key Here!';
 *
 *   // Make sure to catch any errors generated by Disqus.
 *   try {
 *     $disqus = new Disqus($user_api_key);
 *
 *     // Get the forum list.
 *     $forums = $disqus->get_forum_list();
 *
 *     foreach ($forums as $forum) {
 *       echo $forum->name;
 *     }
 *   } catch(DisqusException $exception) {
 *     // Display the error.
 *     echo $exception->getMessage();
 *   }
 * @endcode
 */
class Disqus {
  public $user_api_key = NULL;
  public $forum_api_key = NULL;
  public $api_url = 'http://disqus.com/api/';

  /**
   * Creates a new interface to the Disqus API.
   *
   * @param $user_api_key
   *   (optional) The User API key to use.
   * @param $forum_api_key
   *   (optional) The Forum API key to use.
   * @param $api_url
   *   (optional) The prefix URL to use when calling the Disqus API.
   */
  function __construct($user_api_key = NULL, $forum_api_key = NULL, $api_url = 'http://disqus.com/api/') {
    $this->user_api_key = $user_api_key;
    $this->forum_api_key = $forum_api_key;
    $this->$api_url = $api_url;
  }

  /**
   * Creates a new post on the thread. Does not check against spam filters or ban list. This is intended to allow automated importing of comments.
   *
   * @param $thread_id
   *   the thread to post to
   * @param $message
   *   the content of the post
   * @param $author_name
   *   the post creator’s name
   * @param $author_email
   *   the post creator’s email address
   * @param $parent_post
   *   (optional) the id of the parent post
   * @param $created_at
   *   (optional) the UTC date this post was created, in the format %Y-%m-%dT%H:%M (the current time will be used by default)
   * @param $author_url
   *   (optional) the author's homepage
   * @param $ip_address
   *   (optional) the author’s IP address
   * @return
   *   Returns a Hash containing a representation of the post just created.
   */
  function create_post($thread_id, $message, $author_name, $author_email, $parent_post = NULL, $created_at = NULL, $author_url = NULL, $ip_address = NULL) {
    $arguments = array(
      'thread_id' => $thread_id,
      'message' => $message,
      'author_name' => $author_name,
      'author_email' => $author_email,
      'author_email' => $author_email,
      'parent_post' => $parent_post,
      'created_at' => $created_at,
      'author_url' => $author_url,
      'ip_address' => $ip_address,
    );
    return $this->call('create_post', $arguments, TRUE);
  }

  /**
   * Returns an array of hashes representing all forums the user owns.
   *
   * @return
   *   An array of hashes representing all forums the user owns.
   */
  function get_forum_list() {
    return $this->call('get_forum_list');
  }

  /**
   * Returns A string which is the Forum Key for the given forum.
   *
   * @param $forum_id
   *   the unique id of the forum
   * @return
   *   A string which is the Forum Key for the given forum.
   */
  function get_forum_api_key($forum_id) {
    return $this->call('get_forum_api_key', array('forum_id' => $forum_id));
  }

  /**
   * Returns an array of hashes representing all threads belonging to the given forum.
   *
   * @param $forum_id
   *   the unique id of the forum.
   * @return
   *   An array of hashes representing all threads belonging to the given forum.
   */
  function get_thread_list($forum_id) {
    return $this->call('get_thread_list', array('forum_id' => $forum_id));
  }

  /**
   * Returns a hash having thread_ids as keys and 2-element arrays as values.
   * 
   * The first array element is the number of visible comments on on the thread.
   * This would be useful for showing users of the site (e.g., “5 Comments”).
   * The second array element is the total number of comments on the thread.
   * These numbers are different because some forums require moderator approval,
   * some messages are flagged as spam, etc.
   *
   * @param $thread_ids
   *   an array of thread IDs belonging to the given forum.
   * @return
   *   A hash having thread_ids as keys and 2-element arrays as values.
   */
  function get_num_posts($thread_ids = array()) {
    if (is_array($thread_ids)) {
      $thread_ids = implode(',', $thread_ids);
    }
    return $this->call('get_num_posts', array('thread_ids' => $thread_ids));
  }

  /**
   * Finds threads associated with the given forum.
   *
   * Note that there is no one-to-one mapping between threads and URL’s; a
   * thread will only have an associated URL if it was automatically created by
   * Disqus javascript embedded on that page. Therefore, we recommend using
   * thread_by_identifier whenever possible. This method is provided mainly for
   * handling comments from before your forum was using the API.
   *
   * @param $url
   *   the URL to check for an associated thread
   * @return
   *   Returns a hash representing a thread if one was found, otherwise null.
   */
  function get_thread_by_url($url) {
    return $this->call('get_thread_by_url', array('url' => $url));
  }

  /**
   * Returns an array of hashes representing representing all posts belonging to the given forum.
   *
   * @param $thread_id
   *   The ID of a thread belonging to the given forum
   * @return
   *   An array of hashes representing representing all posts belonging to the
   *   given forum.
   */
  function get_thread_posts($thread_id) {
    return $this->call('get_thread_posts', array('thread_id' => $thread_id));
  }

  /**
   * Create or retrieve a thread by an arbitrary identifying string of your
   * choice. For example, you could use your local database’s ID for the thread.
   * This method allows you to decouple thread identifiers from the URL’s on
   * which they might be appear. (Disqus would normally use a thread’s URL to
   * identify it, which is problematic when URL’s do not uniquely identify a
   * resource.) If no thread exists for the given identifier yet (paired with
   * the forum), one will be created.
   *
   * @param $title
   *   The title of the thread to possibly be created.
   * @param $identifier
   *   A string of your choosing.
   * @return
   *   Returns a hash with two keys:
   *   - thread: a hash representing the thread corresponding to the identifier.
   *   - created: indicates whether the thread was created as a result of this
   *     method call. If created, it will have the specified title.
   */
  function thread_by_identifier($title, $identifier) {
    return $this->call('thread_by_identifier', array('title' => $title, 'identifier' => $identifier), TRUE);
  }

  /**
   * Sets the provided values on the thread object.
   *
   * @param $thread_id
   *   the ID of a thread belonging to the given forum
   * @param $title
   *   the title of the thread
   * @param $slug
   *   the per-forum-unique string used for identifying this thread in disqus.com URL’s relating to this thread. Composed of underscore-separated alphanumeric strings.
   * @param $url
   *   the URL this thread is on, if known.
   * @param $allow_comments
   *   whether this thread is open to new comments
   * @return
   *   Returns an empty success message.
   */
  function update_thread($thread_id, $title = NULL, $slug = NULL, $url = NULL, $allow_comments = NULL) {
    return $this->call('update_thread', array('thread_id' => $thread_id, 'title' => $title, 'slug' => $slug, 'url' => $url, 'allow_comments' => $allow_comments), TRUE);
  }

  /**
   * Makes a call to a Disqus API method.
   *
   * @return 
   *   The Disqus object.
   * @param $method
   *   The Disqus API method to call.
   * @param object $arguments
   *   An associative array of arguments to be passed.
   * @param $post
   *   TRUE or FALSE, depending on whether we're making a POST call.
   */
  function call($function, $arguments = array(), $post = FALSE) {
    // Construct the arguments.
    $args = '';
    if (!isset($arguments['user_api_key'])) {
      $arguments['user_api_key'] = $this->user_api_key;
    }
    if (!isset($arguments['forum_api_key'])) {
      $arguments['forum_api_key'] = $this->forum_api_key;
    }
    foreach ($arguments as $argument => $value) {
      if (!empty($value)) {
        $args .= $argument .'='. urlencode($value) .'&';
      }
    }

    // Call the Disqus API.
    $ch = curl_init();
    if ($post) {
      curl_setopt($ch, CURLOPT_URL, $this->api_url . $function .'/');
      curl_setopt($ch, CURLOPT_POSTFIELDS, $args);
      curl_setopt($ch, CURLOPT_POST, 1);
    }
    else {
      curl_setopt($ch, CURLOPT_URL, $this->api_url . $function .'/?'. $args);
    }
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    $data = curl_exec($ch);
    $info = curl_getinfo($ch);
    curl_close($ch);
    
    // Check the results for errors (200 is a successful HTTP call).
    if ($info['http_code'] == 200) {
      $disqus = json_decode($data);
      if ($disqus->succeeded) {
        // There weren't any errors, so return the results.
        return $disqus->message;
      }
      else {
        throw new DisqusException($disqus->message, 0, $info, $disqus);
      }
    }
    else {
      throw new DisqusException('There was an error querying the Disqus API.', $info['http_code'], $info);
    }
  }
}

/**
 * Any unsucessful result that's created by Disqus API will generate a DisqusException.
 */
class DisqusException extends Exception {
  /**
   * The information returned from the cURL call.
   */
  public $info = NULL;

  /**
   * The information returned from the Disqus call.
   */
  public $disqus = NULL;

  /**
   * Creates a DisqusException.
   * @param $message
   *   The message for the exception.
   * @param $code
   *   (optional) The error code.
   * @param $info
   *   (optional) The result from the cURL call.
   */
  public function __construct($message, $code = 0, $info = NULL, $disqus = NULL) {
    $this->info = $info;
    $this->disqus = $disqus;
    parent::__construct($message, $code);
  }

  /**
   * Converts the exception to a string.
   */
  public function __toString() {
    $code = isset($this->disqus->code) ? $this->disqus->code : (isset($info['http_code']) ? $info['http_code'] : 0);
    $message = $this->getMessage();
    return __CLASS__ .": [$code]: $message\n";
  }
}