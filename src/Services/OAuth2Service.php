<?php
declare(strict_types=1);

namespace Ridibooks\Auth\Services;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Type;
use OAuth2\HttpFoundationBridge\Request as BridgeRequest;
use OAuth2\HttpFoundationBridge\Response as BridgeResponse;
use OAuth2\ResponseInterface;
use OAuth2\Server as OAuth2Server;
use Symfony\Component\HttpFoundation\Request;

class OAuth2Service
{
    /** @var Conenction $db */
    private $connection;

    /** @var OAuth2Server $server */
    private $server;

    /** @var OAuth2ClientGrantService $link_state */
    private $link_state;

    public function __construct(Connection $connection, OAuth2Server $server, OAuth2ClientGrantService $link_state)
    {
        $this->connection = $connection;
        $this->server = $server;
        $this->link_state = $link_state;
    }

    public function validateAuthorizeRequest(Request $request): bool
    {
        $request = BridgeRequest::createFromRequest($request);
        $response = BridgeResponse::create();
        return $this->server->validateAuthorizeRequest($request, $response);
    }

    public function handleAuthorizeRequest(Request $request, int $user_id, bool $is_authorized): ResponseInterface
    {
        $request = BridgeRequest::createFromRequest($request);
        $response = BridgeResponse::create();
        if (!$this->server->validateAuthorizeRequest($request, $response)) {
            return $this->server->getResponse();
        }

        return $this->server->handleAuthorizeRequest($request, $response, $is_authorized, $user_id);
    }

    public function handleTokenRequest(Request $request): ResponseInterface
    {
        $bridge_request = BridgeRequest::createFromRequest($request);
        $bridge_response = BridgeResponse::create();
        return $this->server->handleTokenRequest($bridge_request, $bridge_response);
    }

    public function handleRevokeRequest(Request $request): ResponseInterface
    {
        $bridge_request = BridgeRequest::createFromRequest($request);
        $bridge_response = BridgeResponse::create();
        return $this->server->handleRevokeRequest($bridge_request, $bridge_response);
    }

    public function getTokenData(Request $request)
    {
        $bridge_request = BridgeRequest::createFromRequest($request);
        $bridge_response = BridgeResponse::create();
        if (!$this->server->verifyResourceRequest($bridge_request, $bridge_response)) {
            return null;
        }

        return $this->server->getAccessTokenData($bridge_request, $bridge_response);
    }

    public function getResponse(): ResponseInterface
    {
        return $this->server->getResponse();
    }

    public function isGrantedClient(int $user_id, string $client_id): bool
    {
        return $this->link_state->isGrantedClient($user_id, $client_id);
    }

    public function grant(int $user_id, string $client_id)
    {
        $this->link_state->grant($user_id, $client_id);
    }

    public function deny(int $user_id, string $client_id)
    {
        $this->link_state->deny($user_id, $client_id);
        $this->revokeAllAuthorizationCode($user_id, $client_id);
        $this->revokeAllToken($user_id, $client_id);
    }

    private function revokeAllAuthorizationCode(int $user_id, string $client_id)
    {
        $this->connection->delete('oauth_authorization_codes', [
            'user_id' => $user_id,
            'client_id' => $client_id,
        ], [Type::STRING, Type::STRING]);
    }

    private function revokeAllToken(int $user_id, string $client_id)
    {
        $this->connection->delete('oauth_access_tokens', [
            'user_id' => $user_id,
            'client_id' => $client_id,
        ], [Type::STRING, Type::STRING]);
        $this->connection->delete('oauth_refresh_tokens', [
            'user_id' => $user_id,
            'client_id' => $client_id,
        ], [Type::STRING, Type::STRING]);
    }
}
