<?php

require_once 'CRM/Core/Page.php';

/**
 * An example page which queues several tasks and then executes them
 */
class CRM_Demoqueue_Page_DemoQueue extends CRM_Core_Page {
  const QUEUE_NAME = 'demo-queue';

  function run() {
    $queue = CRM_Queue_Service::singleton()->create(array(
      'type' => 'Sql',
      'name' => self::QUEUE_NAME,
      'reset' => TRUE,
    ));

    for ($i = 0; $i < 5; $i++) {
      $queue->createItem(new CRM_Queue_Task(
        array('CRM_Demoqueue_Page_DemoQueue', 'doMyWork'), // callback
        array($i, "Task $i takes $i second(s)"), // arguments
        "Task $i" // title
      ));
      if ($i == 2) {
        $queue->createItem(new CRM_Queue_Task(
          array('CRM_Demoqueue_Page_DemoQueue', 'addMoreWork'), // callback
          array(), // arguments
          "Add More Work" // title
        ));
      }
    }

    $runner = new CRM_Queue_Runner(array(
      'title' => ts('Demo Queue Runner'),
      'queue' => $queue,
      'onEnd' => array('CRM_Demoqueue_Page_DemoQueue', 'onEnd'),
      'onEndUrl' => CRM_Utils_System::url('civicrm/demo-queue/done'),
    ));
    $runner->runAllViaWeb(); // does not return
  }

  /**
   * Perform some business logic
   */
  static function doMyWork(CRM_Queue_TaskContext $ctx, $delay, $message) {
    sleep(1);
    //sleep($delay);
    //$ctx->log->info($message); // PEAR Log interface
    //$ctx->logy->info($message); // PEAR Log interface -- broken, PHP error
    //CRM_Core_DAO::executeQuery('select from alsdkjfasdf'); // broken, PEAR error
    //throw new Exception('whoz'); // broken, exception
    return TRUE; // success
  }

  /**
   * Perform some business logic
   */
  static function addMoreWork(CRM_Queue_TaskContext $ctx) {
    sleep(1);
    for ($i = 0; $i < 5; $i++) {
      $ctx->queue->createItem(new CRM_Queue_Task(
        array('CRM_Demoqueue_Page_DemoQueue', 'doMyWork'), // callback
        array($i, "Extra task $i takes $i second(s)"), // arguments
        "Extra Task $i" // title
      ), array(
        'weight' => -1,
      ));
    }
    return TRUE; // success
  }

  /**
   * Handle the final step of the queue
   */
  static function onEnd(CRM_Queue_TaskContext $ctx) {
    //CRM_Utils_System::redirect('civicrm/demo-queue/done');
    CRM_Core_Error::debug_log_message('finished task');
    //$ctx->logy->info($message); // PEAR Log interface -- broken, PHP error
    //CRM_Core_DAO::executeQuery('select from alsdkjfasdf'); // broken, PEAR error
    //throw new Exception('whoz'); // broken, exception
  }
}
