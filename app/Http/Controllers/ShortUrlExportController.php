<?php

namespace App\Http\Controllers;

use App\Models\ShortUrl;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ShortUrlExportController extends Controller
{
    public function __invoke(Request $request): StreamedResponse
    {
        $this->authorize('export', ShortUrl::class);

        $user = $request->user();
        $interval = $request->query('interval');

        $query = ShortUrl::with(['user', 'company'])
            ->visibleTo($user)
            ->inInterval($interval)
            ->latest();

        $filename = 'short-urls-'.now()->format('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($query, $user) {
            $handle = fopen('php://output', 'w');

            $headers = ['Short URL', 'Long URL', 'Hits', 'Created On'];

            if ($user->isSuperAdmin()) {
                $headers[] = 'Client';
            } elseif ($user->isAdmin()) {
                $headers[] = 'Created By';
            }

            fputcsv($handle, $headers);

            // Chunk to avoid pulling the entire result set into memory.
            $query->chunk(500, function ($rows) use ($handle, $user) {
                foreach ($rows as $row) {
                    $line = [
                        url('/s/'.$row->short_code),
                        $row->original_url,
                        $row->hits,
                        $row->created_at->format("d M 'y"),
                    ];

                    if ($user->isSuperAdmin()) {
                        $line[] = $row->company->name ?? '';
                    } elseif ($user->isAdmin()) {
                        $line[] = $row->user->name ?? '';
                    }

                    fputcsv($handle, $line);
                }
            });

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }
}
