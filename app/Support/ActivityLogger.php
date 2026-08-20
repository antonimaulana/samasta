<?php

namespace App\Support;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class ActivityLogger
{
    public static function logRequest(Request $request): void
    {
        $user = $request->user();

        if (! $user || $request->isMethodSafe()) {
            return;
        }

        $route = $request->route();
        $action = $route?->getName() ?? $request->path();
        [$subjectType, $subjectId] = self::resolveSubject($route?->parameters() ?? []);

        ActivityLog::create([
            'user_id' => $user->id,
            'action' => $action,
            'method' => $request->method(),
            'subject_type' => $subjectType,
            'subject_id' => $subjectId,
            'description' => self::describe($action, $request->method()),
            'ip_address' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 500),
        ]);
    }

    /**
     * @param  array<string, mixed>  $parameters
     * @return array{0: ?string, 1: ?int}
     */
    private static function resolveSubject(array $parameters): array
    {
        foreach ($parameters as $parameter) {
            if ($parameter instanceof Model) {
                return [$parameter->getMorphClass(), $parameter->getKey()];
            }
        }

        return [null, null];
    }

    private static function describe(string $action, string $method): string
    {
        return strtoupper($method).' '.$action;
    }
}
