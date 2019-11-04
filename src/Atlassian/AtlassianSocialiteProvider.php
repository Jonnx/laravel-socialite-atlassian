<?php

namespace Atlassian;

use Laravel\Socialite\Two\AbstractProvider;
use Laravel\Socialite\Two\ProviderInterface;
use Laravel\Socialite\Two\User;

class AtlassianSocialiteProvider extends AbstractProvider implements ProviderInterface
{
    protected $scopeSeparator = ' ';

    protected $accessTokenResponseBody;

    /**
     * {@inheritdoc}
     */
    protected function getAuthUrl($state)
    {
        return $this->buildAuthUrlFromBase('https://auth.atlassian.com/authorize', $state);
    }
    /**
     * {@inheritdoc}
     */
    protected function getTokenUrl()
    {
        return 'https://auth.atlassian.com/oauth/token';
    }

    protected function getTokenFields($code)
    {
        return [
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'code' => $code,
            'redirect_uri' => $this->redirectUrl,
            'grant_type' => 'authorization_code',
        ];
    }

    /**
     * Get the access token response for the given code.
     *
     * @param  string  $code
     * @return array
     */
    public function getAccessTokenResponse($code)
    {
        $response = $this->getHttpClient()->post($this->getTokenUrl(), [
            'headers' => ['Accept' => 'application/json'],
            'form_params' => $this->getTokenFields($code),
        ]);

        $responseBody = json_decode($response->getBody(), true);
        $this->accessTokenResponseBody = $responseBody;
        return $responseBody;
    }

    protected function getCodeFields($state = null)
    {
        $fields = [
            'audience' => 'api.atlassian.com',
            'client_id' => $this->clientId,
            'scope' => $this->formatScopes($this->getScopes(), $this->scopeSeparator),
            'redirect_uri' => $this->redirectUrl,
            'response_type' => 'code',
            'prompt' => 'consent',
        ];

        if ($this->usesState()) {
            $fields['state'] = $state;
        }

        return array_merge($fields, $this->parameters);
    }

    /**
     * {@inheritdoc}
     */
    protected function getUserByToken($token)
    {
        $response = $this->getHttpClient()->get('https://api.atlassian.com/me', [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
            ],
        ]);

        $user = json_decode($response->getBody(), true);
        $user['token'] = $token;

        return $user;
    }

    /**
     * {@inheritdoc}
     */
    protected function mapUserToObject(array $user)
    {
        return (new User)->setRaw($user)->map([
            'name' => $user['name'],
            'email' => $user['email'],
            'photo_url' => $user['picture'],
            'accessTokenResponseBody' => $this->accessTokenResponseBody,
        ]);
    }
}
