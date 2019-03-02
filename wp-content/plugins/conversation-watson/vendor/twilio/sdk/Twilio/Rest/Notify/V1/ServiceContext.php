<?php

/**
 * This code was generated by
 * \ / _    _  _|   _  _
 * | (_)\/(_)(_|\/| |(/_  v1.0.0
 * /       /
 */

namespace Twilio\Rest\Notify\V1;

use Twilio\Exceptions\TwilioException;
use Twilio\InstanceContext;
use Twilio\Options;
use Twilio\Rest\Notify\V1\Service\BindingList;
use Twilio\Rest\Notify\V1\Service\NotificationList;
use Twilio\Rest\Notify\V1\Service\SegmentList;
use Twilio\Rest\Notify\V1\Service\UserList;
use Twilio\Serialize;
use Twilio\Values;
use Twilio\Version;

/**
 * PLEASE NOTE that this class contains beta products that are subject to change. Use them with caution.
 * 
 * @property \Twilio\Rest\Notify\V1\Service\BindingList bindings
 * @property \Twilio\Rest\Notify\V1\Service\NotificationList notifications
 * @property \Twilio\Rest\Notify\V1\Service\UserList users
 * @property \Twilio\Rest\Notify\V1\Service\SegmentList segments
 * @method \Twilio\Rest\Notify\V1\Service\BindingContext bindings(string $sid)
 * @method \Twilio\Rest\Notify\V1\Service\UserContext users(string $identity)
 */
class ServiceContext extends InstanceContext {
    protected $_bindings = null;
    protected $_notifications = null;
    protected $_users = null;
    protected $_segments = null;

    /**
     * Initialize the ServiceContext
     * 
     * @param \Twilio\Version $version Version that contains the resource
     * @param string $sid The sid
     * @return \Twilio\Rest\Notify\V1\ServiceContext 
     */
    public function __construct(Version $version, $sid) {
        parent::__construct($version);

        // Path Solution
        $this->solution = array('sid' => $sid,);

        $this->uri = '/Services/' . rawurlencode($sid) . '';
    }

    /**
     * Deletes the ServiceInstance
     * 
     * @return boolean True if delete succeeds, false otherwise
     */
    public function delete() {
        return $this->version->delete('delete', $this->uri);
    }

    /**
     * Fetch a ServiceInstance
     * 
     * @return ServiceInstance Fetched ServiceInstance
     */
    public function fetch() {
        $params = Values::of(array());

        $payload = $this->version->fetch(
            'GET',
            $this->uri,
            $params
        );

        return new ServiceInstance($this->version, $payload, $this->solution['sid']);
    }

    /**
     * Update the ServiceInstance
     * 
     * @param array|Options $options Optional Arguments
     * @return ServiceInstance Updated ServiceInstance
     */
    public function update($options = array()) {
        $options = new Values($options);

        $data = Values::of(array(
            'FriendlyName' => $options['friendlyName'],
            'ApnCredentialSid' => $options['apnCredentialSid'],
            'GcmCredentialSid' => $options['gcmCredentialSid'],
            'MessagingServiceSid' => $options['messagingServiceSid'],
            'FacebookMessengerPageId' => $options['facebookMessengerPageId'],
            'DefaultApnNotificationProtocolVersion' => $options['defaultApnNotificationProtocolVersion'],
            'DefaultGcmNotificationProtocolVersion' => $options['defaultGcmNotificationProtocolVersion'],
            'FcmCredentialSid' => $options['fcmCredentialSid'],
            'DefaultFcmNotificationProtocolVersion' => $options['defaultFcmNotificationProtocolVersion'],
            'LogEnabled' => Serialize::booleanToString($options['logEnabled']),
            'AlexaSkillId' => $options['alexaSkillId'],
            'DefaultAlexaNotificationProtocolVersion' => $options['defaultAlexaNotificationProtocolVersion'],
        ));

        $payload = $this->version->update(
            'POST',
            $this->uri,
            array(),
            $data
        );

        return new ServiceInstance($this->version, $payload, $this->solution['sid']);
    }

    /**
     * Access the bindings
     * 
     * @return \Twilio\Rest\Notify\V1\Service\BindingList 
     */
    protected function getBindings() {
        if (!$this->_bindings) {
            $this->_bindings = new BindingList($this->version, $this->solution['sid']);
        }

        return $this->_bindings;
    }

    /**
     * Access the notifications
     * 
     * @return \Twilio\Rest\Notify\V1\Service\NotificationList 
     */
    protected function getNotifications() {
        if (!$this->_notifications) {
            $this->_notifications = new NotificationList($this->version, $this->solution['sid']);
        }

        return $this->_notifications;
    }

    /**
     * Access the users
     * 
     * @return \Twilio\Rest\Notify\V1\Service\UserList 
     */
    protected function getUsers() {
        if (!$this->_users) {
            $this->_users = new UserList($this->version, $this->solution['sid']);
        }

        return $this->_users;
    }

    /**
     * Access the segments
     * 
     * @return \Twilio\Rest\Notify\V1\Service\SegmentList 
     */
    protected function getSegments() {
        if (!$this->_segments) {
            $this->_segments = new SegmentList($this->version, $this->solution['sid']);
        }

        return $this->_segments;
    }

    /**
     * Magic getter to lazy load subresources
     * 
     * @param string $name Subresource to return
     * @return \Twilio\ListResource The requested subresource
     * @throws \Twilio\Exceptions\TwilioException For unknown subresources
     */
    public function __get($name) {
        if (property_exists($this, '_' . $name)) {
            $method = 'get' . ucfirst($name);
            return $this->$method();
        }

        throw new TwilioException('Unknown subresource ' . $name);
    }

    /**
     * Magic caller to get resource contexts
     * 
     * @param string $name Resource to return
     * @param array $arguments Context parameters
     * @return \Twilio\InstanceContext The requested resource context
     * @throws \Twilio\Exceptions\TwilioException For unknown resource
     */
    public function __call($name, $arguments) {
        $property = $this->$name;
        if (method_exists($property, 'getContext')) {
            return call_user_func_array(array($property, 'getContext'), $arguments);
        }

        throw new TwilioException('Resource does not have a context');
    }

    /**
     * Provide a friendly representation
     * 
     * @return string Machine friendly representation
     */
    public function __toString() {
        $context = array();
        foreach ($this->solution as $key => $value) {
            $context[] = "$key=$value";
        }
        return '[Twilio.Notify.V1.ServiceContext ' . implode(' ', $context) . ']';
    }
}