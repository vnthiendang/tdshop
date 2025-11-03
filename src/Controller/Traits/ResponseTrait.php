<?php
/**
 * ResponseTrait.php
 * 
 * Common response handling methods for controllers.
 *
**/
namespace App\Controller\Traits;

trait ResponseTrait
{
    protected function respondSuccess(array $payload = [], ?string $redirect = null)
    {
        if ($this->request->is('ajax')) {
            return $this->response->withType('application/json')
                ->withStringBody(json_encode([
                    'success' => true,
                    'message' => $payload['message'] ?? 'Success',
                    'data' => $payload['data'] ?? null
                ]))
                ->withStatus(200);
        }

        $this->Flash->success($payload['message'] ?? 'Success');

        if ($redirect === false) {
            return null;
        }

        return $this->redirect($redirect ?? ['action' => 'index']);
    }

    protected function respondError(string $message, int $status = 400, ?string $redirect = null)
    {
        if ($this->request->is('ajax')) {
            return $this->response->withType('application/json')
                ->withStringBody(json_encode([
                    'success' => false,
                    'error' => $message
                ]))
                ->withStatus($status);
        }

        $this->Flash->error($message);

        if ($redirect === false) {
            // Không redirect
            return null;
        }

        return $this->redirect($redirect ?? ['action' => 'index']);
    }
}
