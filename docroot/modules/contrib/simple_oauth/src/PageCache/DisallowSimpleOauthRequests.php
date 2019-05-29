<?php

namespace Drupal\simple_oauth\PageCache;

use Symfony\Component\HttpFoundation\Request;

/**
 * Do not serve a page from cache if OAuth2 authentication is applicable.
 *
 * @internal
 */
class DisallowSimpleOauthRequests implements SimpleOauthRequestPolicyInterface {

  /**
   * {@inheritdoc}
   */
  public function isOauth2Request(Request $request) {
    // Check the header. See: http://tools.ietf.org/html/rfc6750#section-2.1
    //
    // Patching to override OAuth request cache deny.
    if (strpos(trim($request->headers->get('Authorization', '', TRUE)), 'Bearer ') !== FALSE) {
      if ($request->headers->get('CACHE_DISALLOW') == 'override') {
        $authProvider = \Drupal::service('simple_oauth.server.resource_server');
        try {
          if ($authProvider->validateAuthenticatedRequest($request)) {
            return static::ALLOW;
          }
        }
        catch (\Exception $e) {
          return static::DENY;
        }
      }
      return static::DENY;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function check(Request $request) {
    $check = $this->isOauth2Request($request);
    return $check ? $check : NULL;
  }

}
