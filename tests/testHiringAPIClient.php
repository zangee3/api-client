<?php
require_once('lib/HiringApiClient.class.php');

class testHiringAPIClient extends PHPUnit_Framework_TestCase {

  protected $apiClient;

  protected function setUp() {
    $this->apiClient = new Hiring_API_Client();
  }

  /**
   * @dataProvider setProvider
   */
  public function testCanSetKeys($key, $value) {
    $params = array($key, $value);

    $this->expectOutputString('ok');
    print $this->apiClient->set($params);
  }

  public function setProvider()
  {
    return array(
      array('foo', 'bar'),
      array('bar', 'baz'),
      array('mat', 'gilbert')
    );
  }

  public function testCanListKeys() {
    $this->assertContains('foo', $this->apiClient->listKeys());
  }

  /**
   * @dataProvider getProvider
   */
  public function testCanGetKeys() {
    $params = array('foo');

    $this->expectOutputString('bar');
    print $this->apiClient->get($params);
  }

  public function getProvider()
  {
    return array(
      array('foo', 'bar'),
      array('bar', 'baz'),
      array('mat', 'gilbert'),
      array('invalid', 'unknown key invalid')
    );
  }

  public function testCanDeleteKeys() {
    $params = array('foo');
    $this->expectOutputString('ok');
    print $this->apiClient->delete($params);
  }

  public function testCanListKeysAfterDelete() {
    $regex='/^((?!foo).)*$/s';
    $this->expectOutputRegex($regex);
    print $this->apiClient->listKeys();
  }
  
  /**
   * @expectedException        Exception
   * @expectedExceptionMessage Error: validate_command() - command not found
   */
  public function testInvalidCommandException() {
    $params = array();
    $this->apiClient->validate_command('fetch', $params);
  }

  /**
   * @expectedException        Exception
   * @expectedExceptionMessage Error: validate_command() - Missing parameter for action
   */
  public function testInvalidParametersException() {
    $params = array();
    $this->apiClient->validate_command('set', $params);
  }

}