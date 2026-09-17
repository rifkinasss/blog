<?php

namespace App\Http\Middleware;

use App\Models\ApiToken;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateApiToken
{
    public function handle(Request $request, Closure $next, string ...$abilities): Response
    {
        $plainToken = $request->bearerToken();

        if (! $plainToken) {
            return response()->json(['message' => 'Bearer token is required.'], 401);
        }

        $token = ApiToken::query()
            ->with(['user', 'serviceAccount.contentUser'])
            ->where('token_hash', hash('sha256', $plainToken))
            ->first();

        if (! $token || $token->expires_at?->isPast() || ($token->serviceAccount && ! $token->serviceAccount->enabled)) {
            return response()->json(['message' => 'Token is invalid or expired.'], 401);
        }

        foreach ($abilities as $ability) {
            $alternatives = explode('|', $ability);

            if (! collect($alternatives)->contains(fn (string $candidate): bool => $token->can($candidate))) {
                return response()->json(['message' => "Token lacks the {$ability} ability."], 403);
            }
        }

        $token->forceFill(['last_used_at' => now()])->saveQuietly();
        $request->attributes->set('apiToken', $token);
        $request->setUserResolver(fn () => $token->actor());

        return $next($request);
    }
}
