<?php
require_once('./phirehose/phirehose_0.2.4/lib/Phirehose.php');
/**
 * Example of how to update filter predicates using Phirehose 
 */
class DynamicTrackConsumer extends Phirehose
{

  /**
   * Subclass specific attribs
   */

  function DynamicTrackConsumer($username, $password, $method = Phirehose::METHOD_SAMPLE, $format = self::FORMAT_JSON) {
    parent::__construct($username, $password, $method, $format);

    $this->msgque = msg_get_queue(6367);

  }


  /**
   * Enqueue each status
   *
   * @param string $status
   */
  public function enqueueStatus($status)
  {
    $data = json_decode($status, true);
    echo $status . "\n";
    msg_send($this->msgque, 1, $status);
  }
  
  /**
   * In this example, we just set the track words to a random 2 words. In a real example, you'd want to check some sort
   * of shared medium (ie: memcache, DB, filesystem) to determine if the filter has changed and set appropriately. The
   * speed of this method will affect how quickly you can update filters. 
   */
  public function checkFilterPredicates()
  {

  }
  
}

// Start streaming
$sc = new DynamicTrackConsumer("username", "password", Phirehose::METHOD_USERSTREAM);
$sc->consume();