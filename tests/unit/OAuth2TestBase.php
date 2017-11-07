<?php
declare(strict_types=1);

namespace Ridibooks\Tests\Auth;

use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Types\Type;
use OAuth2\GrantType\AuthorizationCode;
use OAuth2\GrantType\RefreshToken;
use OAuth2\GrantType\UserCredentials;
use OAuth2\Server as OAuth2Server;
use OAuth2\Storage\Pdo as DefaultStorage;
use PHPUnit\Framework\TestCase;
use Ridibooks\Auth\Library\UserCredentialStorage;
use Ridibooks\Auth\Services\OAuth2ClientGrantService;
use Ridibooks\Auth\Services\OAuth2Service;
use Symfony\Component\HttpFoundation\Request;

abstract class OAuth2TestBase extends TestCase
{
    const AUTHORIZE_PATH = 'http://ridibooks.com/auth/oauth2/authorize';
    const TOKEN_PATH = 'http://ridibooks.com/auth/oauth2/token';
    const REVOKE_PATH = 'http://ridibooks.com/auth/oauth2/revoke';
    const RESOURCE_PATH = 'http://ridibooks.com/api/some/resource';

    const CLIENT_ID = 'test_client';
    const CLIENT_SECRET = 'test_client_secret';
    const CLIENT_REDIRECT_URI = 'http://fake.com';

    const AUTHORIZE_STATE = 'test_state';

    const USER_IDX = 1;
    const USER_ID = 'testuser';
    const USER_PASS = '112233';

    const AUTHORIZE_CODE = 'test_authorize_code';
    const AUTHORIZE_CODE_EXPIRED = 'test_authorize_code_expired';
    const ACCESS_TOKEN = 'test_access_token';
    const ACCESS_TOKEN_EXPIRED = 'test_access_token_expired';
    const REFRESH_TOKEN = 'test_refresh_token';
    const REFRESH_TOKEN_EXPIRED = 'test_refresh_token_expired';

    protected static function getDB()
    {
        return [
            'default' => [
                'host' => $_ENV['OAUTH_DB_HOST'],
                'dbname' => $_ENV['OAUTH_DB_DBNAME'],
                'user' => $_ENV['OAUTH_DB_USER'],
                'password' => $_ENV['OAUTH_DB_PASSWORD'],
                'driver' => 'pdo_mysql',
                'charset' => 'utf8',
            ],
            'user_credential' => [
                'host' => $_ENV['USER_DB_HOST'],
                'dbname' => $_ENV['USER_DB_DBNAME'],
                'user' => $_ENV['USER_DB_USER'],
                'password' => $_ENV['USER_DB_PASSWORD'],
                'driver' => 'pdo_mysql',
                'charset' => 'utf8',
            ]
        ];
    }

    protected static function getConnection($index)
    {
        $db = self::getDB();
        $config = new Configuration();
        return DriverManager::getConnection($db[$index], $config);
    }

    protected static function getPDO(array $db) {
        $dsn = 'mysql:dbname=' . $db['dbname'] . ';';
        $dsn .= 'host=' . $db['host'] . ';';
        return new \PDO($dsn, $db['user'], $db['password']);
    }

    protected function createOAuth2Server($storage): OAuth2Server
    {
        $server = new OAuth2Server($storage, [
            'auth_code_lifetime' => $_ENV['OAUTH_CODE_LIFETIME'],
            'access_lifetime' => $_ENV['OAUTH_ACCESS_LIFETIME'],
            'refresh_token_lifetime' => $_ENV['OAUTH_REFRESH_TOKEN_LIFETIME'],
            'enforce_state' => true,
            'require_exact_redirect_uri' => true,
        ]);

        $server->addGrantType(new AuthorizationCode($storage['authorization_code']));
        $server->addGrantType(new UserCredentials($storage['user_credentials']));
        $server->addGrantType(new RefreshToken($storage['refresh_token'], [
            'always_issue_new_refresh_token' => true
        ]));

        return $server;
    }

    protected function createOAuth2Service(): OAuth2Service
    {
        $db = self::getDB();
        $default_connection = self::getPDO($db['default']);
        $default_storage = new DefaultStorage($default_connection);

        $user_credential_db = isset($db['user_credential']) ? $db['user_credential'] : $db['default'];
        $user_credential_storage = new UserCredentialStorage($user_credential_db);

        $storage = [
            'access_token' => $default_storage, // OAuth2\Storage\AccessTokenInterface
            'authorization_code' => $default_storage, // OAuth2\Storage\AuthorizationCodeInterface
            'client_credentials' => $default_storage, // OAuth2\Storage\ClientCredentialsInterface
            'client' => $default_storage, // OAuth2\Storage\ClientInterface
            'refresh_token' => $default_storage, // OAuth2\Storage\RefreshTokenInterface
            'user_credentials' => $user_credential_storage, // OAuth2\Storage\UserCredentialsInterface
            'user_claims' => $default_storage, // OAuth2\OpenID\Storage\UserClaimsInterface
            'public_key' => $default_storage, // OAuth2\Storage\PublicKeyInterface
            'jwt_bearer' => $default_storage, // OAuth2\Storage\JWTBearerInterface
            'scope' => $default_storage, // OAuth2\Storage\ScopeInterface
        ];

        $server = self::createOAuth2Server($storage);
        $link_state = new OAuth2ClientGrantService(self::getConnection('default'));
        return new OAuth2Service(self::getConnection('default'), $server, $link_state);
    }

    protected static function createClient()
    {
        self::cleanClient();

        $db = self::getConnection('default');
        $db->insert(
            'oauth_clients',
            [
                'client_id' => self::CLIENT_ID,
                'client_secret' => self::CLIENT_SECRET,
                'redirect_uri' => self::CLIENT_REDIRECT_URI,
            ],
            [Type::STRING, Type::STRING, Type::STRING]
        );
    }

    protected static function createAuthorizeCode()
    {
        self::cleanAuthorizeCodes();

        $db = self::getConnection('default');
        $db->insert(
            'oauth_authorization_codes',
            [
                'authorization_code' => self::AUTHORIZE_CODE,
                'client_id' => self::CLIENT_ID,
                'user_id' => self::USER_IDX,
                'redirect_uri' => self::CLIENT_REDIRECT_URI,
                'expires' => new \DateTime('2020-01-01 00:00:00'),
            ],
            [Type::STRING, Type::STRING, Type::INTEGER, Type::STRING, Type::DATETIME]
        );

        $db->insert(
            'oauth_authorization_codes',
            [
                'authorization_code' => self::AUTHORIZE_CODE_EXPIRED,
                'client_id' => self::CLIENT_ID,
                'user_id' => self::USER_IDX,
                'redirect_uri' => self::CLIENT_REDIRECT_URI,
                'expires' => new \DateTime('2017-01-01 00:00:00'),
            ],
            [Type::STRING, Type::STRING, Type::INTEGER, Type::STRING, Type::DATETIME]
        );
    }

    protected static function createToken()
    {
        self::cleanTokens();

        $db = self::getConnection('default');
        $db->insert(
            'oauth_access_tokens',
            [
                'access_token' => self::ACCESS_TOKEN,
                'client_id' => self::CLIENT_ID,
                'user_id' => self::USER_IDX,
                'expires' => new \DateTime('2020-01-01 00:00:00'),
            ],
            [Type::STRING, Type::STRING, Type::INTEGER, Type::DATETIME]
        );

        $db->insert(
            'oauth_access_tokens',
            [
                'access_token' => self::ACCESS_TOKEN_EXPIRED,
                'client_id' => self::CLIENT_ID,
                'user_id' => self::USER_IDX,
                'expires' => new \DateTime('2017-01-01 00:00:00'),
            ],
            [Type::STRING, Type::STRING, Type::INTEGER, Type::DATETIME]
        );
    }

    protected static function createRefreshToken()
    {
        self::cleanRefreshTokens();

        $db = self::getConnection('default');
        $db->insert(
            'oauth_refresh_tokens',
            [
                'refresh_token' => self::REFRESH_TOKEN,
                'client_id' => self::CLIENT_ID,
                'user_id' => self::USER_IDX,
                'expires' => new \DateTime('2020-01-01 00:00:00'),
            ],
            [Type::STRING, Type::STRING, Type::INTEGER, Type::DATETIME]
        );

        $db->insert(
            'oauth_refresh_tokens',
            [
                'refresh_token' => self::REFRESH_TOKEN_EXPIRED,
                'client_id' => self::CLIENT_ID,
                'user_id' => self::USER_IDX,
                'expires' => new \DateTime('2017-01-01 00:00:00'),
            ],
            [Type::STRING, Type::STRING, Type::INTEGER, Type::DATETIME]
        );
    }

    protected static function cleanTokens()
    {
        $db = self::getConnection('default');
        $db->delete(
            'oauth_access_tokens',
            [
                'user_id' => self::USER_IDX,
                'client_id' => self::CLIENT_ID,
            ],
            [Type::INTEGER, Type::STRING]
        );
    }

    protected static function cleanRefreshTokens()
    {
        $db = self::getConnection('default');
        $db->delete(
            'oauth_refresh_tokens',
            [
                'user_id' => self::USER_IDX,
                'client_id' => self::CLIENT_ID,
            ],
            [Type::INTEGER, Type::STRING]
        );
    }

    protected static function cleanAuthorizeCodes()
    {
        $db = self::getConnection('default');
        $db->delete(
            'oauth_authorization_codes',
            [
                'user_id' => self::USER_IDX,
                'client_id' => self::CLIENT_ID,
            ],
            [Type::INTEGER, Type::STRING]
        );
    }

    protected static function cleanClient()
    {
        $db = self::getConnection('default');
        $db->delete(
            'oauth_clients',
            [
                'client_id' => self::CLIENT_ID,
            ],
            [Type::STRING]
        );
    }

    protected function createAuthorizeRequest($param = []): Request
    {
        $default = [
            'response_type' => 'code',
            'client_id' => self::CLIENT_ID,
            'redirect_uri' => self::CLIENT_REDIRECT_URI,
            'state' => self::AUTHORIZE_STATE,
        ];

        $param = array_merge($default, $param);
        return Request::create(self::AUTHORIZE_PATH, 'GET', $param);
    }

    protected function createTokenRequestWithAuthorizationCode($param = []): Request
    {
        $default = [
            'grant_type' => 'authorization_code',
            'client_id' => self::CLIENT_ID,
            'client_secret' => self::CLIENT_SECRET,
            'code' => self::AUTHORIZE_CODE,
            'redirect_uri' => self::CLIENT_REDIRECT_URI,
        ];

        $param = array_merge($default, $param);
        return Request::create(self::TOKEN_PATH, 'POST', $param);
    }

    protected function createTokenRequestWithUserCredentials($param = []): Request
    {
        $default = [
            'grant_type' => 'password',
            'client_id' => self::CLIENT_ID,
            'client_secret' => self::CLIENT_SECRET,
            'username' => self::USER_ID,
            'password' => self::USER_PASS,
        ];

        $param = array_merge($default, $param);
        return Request::create(self::TOKEN_PATH, 'POST', $param);
    }

    protected function createTokenRequestWithRefreshToken($param = []): Request
    {
        $default = [
            'grant_type' => 'refresh_token',
            'client_id' => self::CLIENT_ID,
            'client_secret' => self::CLIENT_SECRET,
            'refresh_token' => self::REFRESH_TOKEN,
        ];

        $param = array_merge($default, $param);
        return Request::create(self::TOKEN_PATH, 'POST', $param);
    }

    protected function createRevokeRequest($param): Request
    {
        $default = [
            'token_type_hint' => 'access_token',
            'token' => self::ACCESS_TOKEN,
        ];

        $param = array_merge($default, $param);
        return Request::create(self::REVOKE_PATH, 'POST', $param);
    }

    protected function createResourceRequest($access_token): Request
    {
        return Request::create(self::RESOURCE_PATH, 'POST', [
            'access_token' => $access_token,
        ]);
    }
}
