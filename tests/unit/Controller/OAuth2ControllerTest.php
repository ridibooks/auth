<?php
declare(strict_types=1);

namespace Ridibooks\Tests\Auth\Controller;

use Ridibooks\Auth\Controller\OAuth2Controller;
use Ridibooks\Tests\Auth\ControllerTestBase;

class OAuth2ControllerTest extends ControllerTestBase
{
    public function setUp()
    {
        parent::setUp();
    }

    public function testAuthorize()
    {
        $mock_request = $this->createMockObject('\Symfony\Component\HttpFoundation\Request', [
            'get' => [
                ['client_id', $this->test_client_id]
            ]
        ]);

        $mock_session = $this->createMockObject('\Symfony\Component\HttpFoundation\Session\Session', [
            'get' => [
                ['user_idx', $this->test_user['idx']],
                ['user_id', $this->test_user['id']],
            ]
        ]);
        $this->setSession($mock_session);

        $mock_oauth2 = $this->createMockObject('\Ridibooks\Auth\Services\OAuth2Service', [
            'validateAuthorizeRequest' => [
                [$mock_request, true],
            ],
            'isGrantedClient' => [
                [[$this->test_user['idx'], $this->test_client_id], false],
            ]
        ]);
        $this->setOAuth2($mock_oauth2);

        $controller = new OAuth2Controller();
        $actual = $controller->authorize($mock_request, $this->test_app);
        $expected = $this->test_app['twig']->render('agreement.twig', [
            'user_id' => $this->test_user['id'],
            'client_name' => $this->test_client_id,
        ]);
        $this->assertEquals($expected, $actual);
    }

    public function testAuthorizeFormSubmitAgree()
    {
        $mock_request = $this->createMockObject('\Symfony\Component\HttpFoundation\Request', [
            'get' => [
                ['client_id', $this->test_client_id],
                ['agree', 1]
            ]
        ]);

        $mock_session = $this->createMockObject('\Symfony\Component\HttpFoundation\Session\Session', [
            'get' => [
                ['user_idx', $this->test_user['idx']],
            ]
        ]);
        $this->setSession($mock_session);

        $mock_oauth2 = $this->createMockObject('\Ridibooks\Auth\Services\OAuth2Service', [
            'validateAuthorizeRequest' => [
                [$mock_request, true],
            ],
            'grant' => [
                [$this->test_user['idx'], $this->test_client_id],
            ],
            'handleAuthorizeRequest' => [
                [[$mock_request, $this->test_user['idx'], true], null]
            ]
        ]);
        $this->setOAuth2($mock_oauth2);

        $controller = new OAuth2Controller();
        $controller->authorizeFormSubmit($mock_request, $this->test_app);
    }

    public function testAuthorizeFormSubmitDeny()
    {
        $mock_request = $this->createMockObject('\Symfony\Component\HttpFoundation\Request', [
            'get' => [
                ['client_id', $this->test_client_id],
                ['agree', 0]
            ]
        ]);

        $mock_session = $this->createMockObject('\Symfony\Component\HttpFoundation\Session\Session', [
            'get' => [
                ['user_idx', $this->test_user['idx']],
            ]
        ]);
        $this->setSession($mock_session);

        $mock_oauth2 = $this->createMockObject('\Ridibooks\Auth\Services\OAuth2Service', [
            'validateAuthorizeRequest' => [
                [$mock_request, true],
            ],
            'deny' => [
                [$this->test_user['idx'], $this->test_client_id],
            ],
            'handleAuthorizeRequest' => [
                [[$mock_request, $this->test_user['idx'], false], null]
            ]
        ]);
        $this->setOAuth2($mock_oauth2);

        $controller = new OAuth2Controller();
        $controller->authorizeFormSubmit($mock_request, $this->test_app);
    }

    public function testToken()
    {
        $mock_request = $this->createMockObject('\Symfony\Component\HttpFoundation\Request');
        $mock_oauth2 = $this->createMockObject('\Ridibooks\Auth\Services\OAuth2Service', [
            'handleTokenRequest' => [
                [$mock_request, null]
            ]
        ]);
        $this->setOAuth2($mock_oauth2);

        $controller = new OAuth2Controller();
        $controller->token($mock_request, $this->test_app);
    }

    public function testRevoke()
    {
        $mock_request = $this->createMockObject('\Symfony\Component\HttpFoundation\Request');
        $mock_oauth2 = $this->createMockObject('\Ridibooks\Auth\Services\OAuth2Service', [
            'handleRevokeRequest' => [
                [$mock_request, null]
            ]
        ]);
        $this->setOAuth2($mock_oauth2);

        $controller = new OAuth2Controller();
        $controller->revoke($mock_request, $this->test_app);
    }
}