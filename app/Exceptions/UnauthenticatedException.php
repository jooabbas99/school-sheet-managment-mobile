<?php

namespace App\Exceptions;

/**
 * The token is missing, expired, revoked or the account was deactivated: the user must log in again.
 */
class UnauthenticatedException extends ApiException {}
