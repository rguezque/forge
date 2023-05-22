<?php declare(strict_types = 1);

namespace Forge\Route;

use function Forge\functions\str_path;

class Authentication {

    /**
     * Users provider
     * 
     * @var Users
     */
    private $users;

    /**
     * @param ?USers $users Users object with login method
     */
    public function __construct(?Users $users = null) {
        $this->users = $users;
    }

    /**
     * Static method to check authentication credentials
     * 
     * @param array $criteria Security parameters
     * @param string $request_uri Requested URI to check
     * @return ?Response
     */
    public static function firewall(array $criteria, string $request_uri): ?Response {
        foreach($criteria as $area) {
            if(preg_match(self::getPattern($area['protect']), $request_uri)) {
                $session = new Session('FAuth');

                if(!$session->has('logged') || !$session->has('username')) {
                    return new RedirectResponse($area['form']);
                }

                if(!in_array($session->get('role'), $area['roles'])) {
                    return new RedirectResponse($area['form']);
                }
            }
        }

        return null;
    }

    /**
     * Do login for new session
     * 
     * @param Request $request Request object
     * @param Response $response Response object
     * @param ?Services $services Services container
     * @return Response
     */
    public function login(Request $request, Response $response, ?Services $services = null): Response {
        $users = $this->users ?? $services->get('users');
        $credentials = $request->getBodyParams();
        $username = $credentials->get('_username');
        $password = $credentials->get('_password');

        // Get the origin form uri
        $origin_form = $_SERVER['REQUEST_URI'];
        $origin_form = substr($origin_form, 0, -strlen('/login'));

        // If redirect field form was not defined get the origin form uri
        $redirect = $credentials->get('_redirect_success') ?? $origin_form;

        if($users->findUser($username, $password)) {
            $session =  new Session('FAuth');
            $session->set('username', $users->getUsername());
            $session->set('role', $users->getRole());
            $session->set('logged', true);

            return new RedirectResponse($redirect);
        } else {
            return new RedirectResponse($origin_form);
        }
    }

    /**
     * Do logout for actual session
     * 
     * @param Request $request Request object
     * @param Response $response Response object
     * @return Response
     */
    public function logout(Request $request, Response $response): Response {
        // Get the origin form uri
        $origin_form = $_SERVER['REQUEST_URI'];
        $origin_form = substr($origin_form, 0, -strlen('/logout'));

        // If a logout redirect was not defined, get the origin form uri
        $redirect = $request->getQueryParams()->get('redirect') ?? $origin_form;
        $session = new Session('FAuth');
        $session->destroy();

        return new RedirectResponse($redirect);
    }

    /**
     * Retrieve the regex pattern to match with requested uri
     * 
     * @param string $path String patch to process
     * @return string
     */
    private static function getPattern(string $path): string {
        $path = str_replace('/', '\/', str_path($path));
        $path = preg_replace('#{(.*?)}#', '(?<$1>(?!.*/).*)', $path);
        
        return '#^'.$path.'#i';
    }
}

?>