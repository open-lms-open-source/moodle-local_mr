<?php
/**
 * @see mr_bootstrap
 */
require_once($CFG->dirroot.'/local/mr/framework/bootstrap.php');

/**
 * @see mr_lock_abstract
 */
require_once($CFG->dirroot.'/local/mr/framework/lock/abstract.php');

/**
 * MR Lock Redis
 *
 * This lock uses Redis to provide a distributed
 * locking mechanism.
 *
 * @package mr
 * @author Mark Nielsen
 */
class mr_lock_redis extends mr_lock_abstract {
    /**
     * Holds the value we set to the key
     *
     * @var string
     */
    protected $keyvalue = NULL;

    public function get() {

        $result = false;
        $this->keyvalue = NULL;

        try {
            $redis = mr_bootstrap::redis();
            $ttl   = (time() + $this->timetolive + 1);

            // Attempt to obtain lock
            if ($result = $redis->setnx($this->uniquekey, $ttl)) {
                $result = true;
            } else if ($value = $redis->get($this->uniquekey)) {
                // Check if the key has expired or is otherwise invalid
                if (!is_number($value) or $value < time()) {
                    $replaced = $redis->getset($this->uniquekey, $ttl);

                    // If this is not equal, it means another process beat us to the getset
                    if ($replaced == $value) {
                        $result = true;
                    }
                }
            }
            if ($result) {
                $this->keyvalue = $ttl;
            }
            $redis->close();
        } catch (RedisException $e) {
            debugging("RedisException caught with message: {$e->getMessage()}", DEBUG_DEVELOPER);
        }
        return $result;
    }

    public function release() {
        // Clear this regardless of what happens
        $keyvalue = $this->keyvalue;
        $this->keyvalue = NULL;

        $result = 1;

        try {
            // If we have the key value, then we did get the lock
            if (!is_null($keyvalue)) {
                $redis = mr_bootstrap::redis();

                // We check to value to make sure the key hasn't expired and been re-aquired by another process
                if ($redis->get($this->uniquekey) == $keyvalue and $keyvalue > time()) {
                    $result = $redis->delete($this->uniquekey);
                }
                $redis->close();
            }
        } catch (RedisException $e) {
            debugging("RedisException caught with message: {$e->getMessage()}", DEBUG_DEVELOPER);
        }
        return ($result == 1);
    }
}