<?php
namespace block_configurable_reports;

class github extends \curl {
	protected $repo = '';

    /**
     * @inheritDoc
     */
    public function __construct($settings = []) {
        parent::__construct($settings);

        $token = get_config('block_configurable_reports', 'repositorytoken');
        if (!empty($token)) {
            $this->set_token($token);
        }
    }

	public function set_repo($repo) {
		$this->repo = $repo;
	}

	/**
	 * Set a basic auth header.
	 *
	 * @param string $username The username to use.
	 * @param string $password The password to use.
	 */
	public function set_basic_auth($username, $password) {
		$value = 'Basic '.base64_encode($username.':'.$password);
		$this->setHeader('Authorization:'. $value);
		return true;
	}

    /**
     * Set an authorisation token to use with requests.
     * This should be a fine-grained or classic personal access token.
     *
     * @param string $token Access token.
     * @return void
     */
    public function set_token(string $token): void {
        /** @noinspection PhpParamsInspection */
        $this->setHeader("Authorization: Bearer $token");
    }

	public function get($endpoint, $params = array(), $options = array()) {
		$url = 'https://api.github.com/repos/';
		$url .= $this->repo;
		$url .= $endpoint;
		$result = parent::get($url, $params, $options);
		return $result;
	}
}