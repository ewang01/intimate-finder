<?php
/*
Contains functions for:
- entity/sn/facebook/login.php
- entity/sn/facebook/logout.php
- script/start_fetching_facebook_user_data.php */

require getLibFilePath("sn.platform.facebook.facebook");

class SNFacebookService {
	public $user;
	private $fbconfig;
	private $facebook;
	
	public function __construct() {
		$this->fbconfig['appid' ]     = '382809748430590';
    		$this->fbconfig['secret']     = 'fb789ce03dd7178aac5905b69a7a7b34';
    		$this->fbconfig['baseurl']    = 'http://cgp.com/facebook_checkin/app/sn/facebook/login/';
    		$this->facebook = new Facebook(array(
      		'appId'  => 	$this -> fbconfig['appid'],
      		'secret' => 	$this -> fbconfig['secret'],
      		'cookie' => true
	    	));
    		//$this->user = $this->facebook->getUser();// You cannot do this
    		
	}
	
	public function setDBDriverForCorePlatform($DBDriver) {
		
	}
	
	public function setDBDriverForLiveSystem($DBDriver) {
		
	}
	
	public function setAccessToken($data){
		$this->facebook->setAccessToken($data['access_token']);
		$this->user = $this->facebook->getUser();
	}
	
	/* START OTHERS FUNCTIONS */
	
	//$data = ?
	//To be used by entity/sn/facebook/login.php
	
	public function login() {
		$para = array(
                'scope'         => PERMISSION,
                'redirect_uri'  => 'http://apps.facebook.com/computational_geo/'
            );
		$loginUrl   = $this->facebook->getLoginUrl($para);
		
		return $loginUrl;
		
		//TODO
	}
	
	//$data = ?
	//To be used by entity/sn/facebook/logout.php
	public function logout($data) {
		$logoutUrl  = $this->facebook->getLogoutUrl();
		//TODO
	}
	
	/* START GET FUNCTIONS */
	
	//$data = [ "user_id" => 123456 , "limit" => 100]
	//To be used by script/start_fetching_facebook_user_data.php
	public function getUserFeeds($data) {
		if($this->user){
			$user_feed = $this->facebook->api('/'.$data["network_user_id"].'/feed', array("limit" => $data['limit']));
			return $user_feed;
		}
		return false;
	}
	
	//$data = [ "limit" => 100]
	//To be used by script/start_fetching_facebook_user_data.php
	public function getUserFriends($data) {
		if($this->user){
			$user_friends = $this->facebook->api('/me/friends', array("limit" => $data['limit']));
			return $user_friends['data'];
		}
	}
	
	//$data = ["network_user_id" => "45154"]
	//To be used by script/start_fetching_facebook_user_data.php
	public function getUserProfile($data) {
		if($this->user){
			$user_profile = $this->facebook->api('/'.$data["user_id"]);
			return $user_profile;
		}
	}
	
	//$data = ?
	//To be used by script/start_fetching_facebook_user_data.php
	public function getUserFeedsUrls($data) {
		//TODO
		
	}
	
	public function getAccessToken() {
		if($this->user){
			$access_token = $this->facebook->getAccessToken();
			return $access_token;
		}
		
	}
	
	//$data = ["network_user_id" => 1232]
	//get the user name of a user
	public function getUserName($data) {
		if($this->user){
			$result = $this->getUserProfile($data);
			return $result['name'];
		}
		
	}
	
	//$data = ["network_user_id" => 1232]
	//get the user pseudo (aka username) of a user
	public function getUserPseudo($data) {
		if($this->user){
			$result = $this->getUserProfile($data);
			if (isset($result['username'])) {
				return $result['username'];
			}
		}
		return false;
	}
	
	public function getUserId() {
		if($this->facebook->getUser()){
			$this->user = $this->facebook->getUser();
			return $this->user;
		}
		return false;
	}
	
	//$data = ["message"=>"user like book"]
	public function publishPostInUserWall($data) {
		//TODO
		$para = array("message" => $data['message']);
		if(isset($data['link'])){
			$para["link"] = $data['link'];
		}
		if($this->user){
			$this->facebook->api('/'.$data["network_user_id"].'/feed', 'POST', $para);
			return;
		}
	}
	
	//$data = ["user_network_id" => 1234566, "object_type" => "movie"]
	
	public function getUserObjectFromFB($data) {
		switch ($data['object_type']) {
			case 'movie':
				return $this->facebook->api('/'.$data["network_user_id"].'/movies');
			break;
			case 'book':
				return $this->facebook->api('/'.$data["network_user_id"].'/books');
			break;
			case 'music':
				return $this->facebook->api('/'.$data["network_user_id"].'/music');
			break;
			case 'game':
				return $this->facebook->api('/'.$data["network_user_id"].'/games');
			break;
			case 'television':
				return $this->facebook->api('/'.$data["network_user_id"].'/television');
			break;
			case 'music':
				return $this->facebook->api('/'.$data["network_user_id"].'/music');
			break;
			case 'activity':
				return $this->facebook->api('/'.$data["network_user_id"].'/activities');
			break;
			case 'interest':
				return $this->facebook->api('/'.$data["network_user_id"].'/interests');
			break;
			//TODO
			//case 'other':
			//	return $this->facebook->api('/'.$data["network_user_id"].'/?');
			//break;
		}
	}
	
	// by si chang
	public function getUserAndFriendsLikes($data){
		if($this->user){
			$queries = array(
				array('method'=>'GET', 'relative_url'=>'/'.$data['network_user_id'].'/likes'),
				array('method'=>'GET', 'relative_url'=>'/'.$data['network_user_id'].'/friends',"omit_response_on_success" => false,'name'=>'get-friends'),
				array('method'=>'GET', 'relative_url'=>'/likes?ids={result=get-friends:$.data.*.id}'),
				array('method'=>'GET', 'relative_url'=>'fql?q=SELECT pic_big, uid FROM user WHERE uid in({result=get-friends:$.data.*.id})')
				);
			return $this->sendBatchRequests($queries,0);
		}
	}
	
	public function getObjectDetailedInfor($data){
		$start = $data['start'];
		$limit = $data['limit'];
		$ids = $data['data'];
		$queries = array();
		for($i=$start ; $i<$start+$limit; $i = $i+20){
			//echo "\n".implode(',',array_slice($ids,$i, 100))."\n";
			$queries[] = array('method' => 'GET', 'relative_url' => '?ids='.(implode(',',array_slice($ids,$i, 20))));
			
		}
		
		return $this->sendBatchRequests($queries,0);
	}
	
	//$data = [network_user_id => 1565165]
	//get the user profile info feeds and list of friends 
	public function getEverythingExceptFriendsFeeds($data) {
		if($this->user){
			$queries = array(
    				array('method' => 'GET', 'relative_url' => '/'.$data['network_user_id']),
    				array('method' => 'GET', 'relative_url' => '/'.$data['network_user_id'].'/feed?limit='.NUMBER_OF_FB_FEEDS_TO_FETCH_LIMIT),
    				
    				array('method' => 'GET', 'relative_url' => '/'.$data['network_user_id'].'/movies'),
    				array('method' => 'GET', 'relative_url' => '/'.$data['network_user_id'].'/books'),
    				array('method' => 'GET', 'relative_url' => '/'.$data['network_user_id'].'/music'),
    				array('method' => 'GET', 'relative_url' => '/'.$data['network_user_id'].'/games'),
    				array('method' => 'GET', 'relative_url' => '/'.$data['network_user_id'].'/television'),
    				array('method' => 'GET', 'relative_url' => '/'.$data['network_user_id'].'/activities'),
    				array('method' => 'GET', 'relative_url' => '/'.$data['network_user_id'].'/interests'),
				array('method' => 'GET', 'relative_url' => '/'.$data['network_user_id'].'/links?limit='.NUMBER_OF_FB_FEEDS_TO_FETCH_LIMIT),
    				
    				array('method' => 'GET', 'relative_url' => '/'.$data['network_user_id'].'/friends?limit='.NUMBER_OF_FB_FRIENDS_TO_FETCH_LIMIT, "omit_response_on_success" => false, "name" => "get-friends")
    				// ------ DEPRECATED ------- 
    				//array('method' => 'GET', 'relative_url' => '?ids={result=get-friends:$.data.*.id}'),
    				//array('method' => 'GET', 'relative_url' => '/fql?q=SELECT+friend_count+FROM+user+where+uid+=+me()'),
    				//array('method' => 'GET', 'relative_url' => '/1213475558/feed?limit=1000')
    				//array('method' => 'GET', 'relative_url' => '/fql?q=SELECT+source_id,message,attachment+FROM+stream+where+source_id+in+(SELECT+uid2+FROM+friend+WHERE+uid1+=+me())+order+by+source_id+limit+0,50'),
    				//array('method' => 'GET', 'relative_url' => '/fql?q=SELECT+source_id,message,attachment+FROM+stream+where+source_id+in+(SELECT+uid2+FROM+friend+WHERE+uid1+=+me())+order+by+source_id+limit+50,50')
			);
			return $this->sendBatchRequests($queries,0);
		}
	}
	
	//$data = [start => 0, limit => 5 , friends => array(0 => 341542, 1 => 65443, 2 => 545524.....)]
	//Function that encapsulates requests to get the profile info and feeds for 5 friends equivalent to 45 requests
	public function getFriendProfileInfoAndFeeds ($data) {
		if($this->user){
			
			$start = $data['start'];
			$limit = $data['limit'];
			$friends = $data['friends'];
			
			$queries = array();
			for ($i = $start; $i < ($start+$limit); $i++) {
				$queryProfile = array('method' => 'GET', 'relative_url' => '/'.$friends[$i]['id']);
				$queryFeeds = array('method' => 'GET', 'relative_url' => '/'.$friends[$i]['id'].'/feed?limit='.NUMBER_OF_FB_FRIENDS_FEEDS_TO_FETCH_LIMIT);
    				$queryObj1 = array('method' => 'GET', 'relative_url' => '/'.$friends[$i]['id'].'/movies');
    				$queryObj2 = array('method' => 'GET', 'relative_url' => '/'.$friends[$i]['id'].'/books');
    				$queryObj3 = array('method' => 'GET', 'relative_url' => '/'.$friends[$i]['id'].'/music');
    				$queryObj4 = array('method' => 'GET', 'relative_url' => '/'.$friends[$i]['id'].'/games');
    				$queryObj5 = array('method' => 'GET', 'relative_url' => '/'.$friends[$i]['id'].'/television');
    				$queryObj6 = array('method' => 'GET', 'relative_url' => '/'.$friends[$i]['id'].'/activities');
    				$queryObj7 = array('method' => 'GET', 'relative_url' => '/'.$friends[$i]['id'].'/interests');
    				$queryObj8 = array('method' => 'GET', 'relative_url' => '/'.$friends[$i]['id'].'/links?limit='.NUMBER_OF_FB_FRIENDS_FEEDS_TO_FETCH_LIMIT);
				
				
				$queries[] = $queryProfile;
				$queries[] = $queryFeeds;
				$queries[] = $queryObj1;
				$queries[] = $queryObj2;
				$queries[] = $queryObj3;
				$queries[] = $queryObj4;
				$queries[] = $queryObj5;
				$queries[] = $queryObj6;
				$queries[] = $queryObj7;
				$queries[] = $queryObj8;
			}
			return $this->sendBatchRequests($queries,0);
		}
	}
	
	
	
	//send batch requests if FB api timeout we retry 4 times before returning false
	//$data is the array of queries to send
	public function sendBatchRequests($data, $tryCount) {
		$tryCount++;
		try {
			// regular execution continues.
			//echo count($data)."\n";
			
			$batchResponse = $this->facebook->api('?batch='.urlencode(json_encode($data)), 'POST');
			
			//Due to sometimes errors from Facebook in the response (Error_code = 1 "Unknown error occured")
			//we apply the same process of retrying for 4 times
			if (isset($batchResponse['error_code'])) {
				if ($tryCount > 20) {
					echo "Tried $tryCount times to fetch data but fail, now exiting.";
					return false;
				}
				echo "Error inside the response :";
				echo "Error code : ".$batchResponse['error_code']." - Message : ".$batchResponse['error_msg']." \n";
				sleep(1);
				echo "Trying again \n";
				$batchResponse = $this->sendBatchRequests($data,$tryCount);
			}
		} catch (Exception $e) {
			if ($tryCount > 20) {
				echo "Tried $tryCount times to fetch data but fail, now exiting.";
				return false;
			}
			echo 'Caught exception: ',  $e->getMessage(), "\n";
			sleep(100);
			echo "Trying again \n";
			$batchResponse = $this->sendBatchRequests($data,$tryCount);
		}
		return $batchResponse;
	}
}
?>
