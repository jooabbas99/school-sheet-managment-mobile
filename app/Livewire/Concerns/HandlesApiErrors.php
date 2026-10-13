<?php

namespace App\Livewire\Concerns;

use App\Exceptions\ApiException;
use App\Exceptions\OfflineException;
use App\Exceptions\UnauthenticatedException;

/**
 * Runs an API call and turns errors into component state:
 * field errors under inputs, an Arabic message in $errorMessage, or a redirect to login.
 */
trait HandlesApiErrors
{
    public ?string $errorMessage = null;

    public bool $offline = false;

    /**
     * @template T
     *
     * @param  callable(): T  $call
     * @return T|null
     */
    protected function callApi(callable $call): mixed
    {
        $this->errorMessage = null;
        $this->offline = false;

        try {
            return $call();
        } catch (UnauthenticatedException) {
            session()->flash('status', __('auth.session_expired'));
            $this->redirectRoute('login');
        } catch (OfflineException $e) {
            $this->offline = true;
            $this->errorMessage = $e->getMessage();
        } catch (ApiException $e) {
            if ($e->isValidation()) {
                foreach ($e->errors as $field => $messages) {
                    foreach ((array) $messages as $message) {
                        $this->addError($field, $message);
                    }
                }
            }

            $this->errorMessage = $e->getMessage();
        }

        return null;
    }
}
