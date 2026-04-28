@extends('layouts.app-dashboard')
@section('title', 'Edit SSO Client')
@section('page-title', 'Edit SSO Client')

@section('content')
<div class="max-w-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl overflow-hidden">
    <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800">
        <h2 class="text-sm font-semibold text-slate-800 dark:text-white">Edit SSO Client: {{ $client->name }}</h2>
    </div>
    
    <form action="{{ route('sso.clients.update', $client->id) }}" method="POST" class="p-6 space-y-4">
        @csrf @method('PUT')
        
        <div>
            <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Application Name</label>
            <input type="text" name="name" value="{{ old('name', $client->name) }}" required class="w-full text-sm rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 text-slate-900 dark:text-white px-3 py-2">
            @error('name') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
        </div>
        
        <div>
            <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">OAuth Redirect URI</label>
            <input type="url" name="redirect" value="{{ old('redirect', $oauthClient->redirect ?? '') }}" required placeholder="http://client.app/auth/callback" class="w-full text-sm rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 text-slate-900 dark:text-white px-3 py-2">
            <p class="text-[10px] text-slate-500 mt-1">Dipisahkan koma jika lebih dari satu.</p>
            @error('redirect') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Global Logout Webhook URL (Optional)</label>
            <input type="url" name="webhook_url" value="{{ old('webhook_url', $client->webhook_url) }}" placeholder="http://client.app/auth/sso/logout-callback" class="w-full text-sm rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 text-slate-900 dark:text-white px-3 py-2">
            @error('webhook_url') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Description</label>
            <textarea name="description" rows="3" class="w-full text-sm rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 text-slate-900 dark:text-white px-3 py-2">{{ old('description', $client->description) }}</textarea>
            @error('description') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="inline-flex items-center">
                <input type="checkbox" name="is_active" value="1" {{ $client->is_active ? 'checked' : '' }} class="rounded border-slate-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                <span class="ml-2 text-sm text-slate-600 dark:text-slate-400">Active</span>
            </label>
        </div>
        
        <div class="pt-4 flex justify-end gap-2">
            <a href="{{ route('sso.clients.index') }}" class="px-4 py-2 text-sm text-slate-600 dark:text-slate-400">Cancel</a>
            <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg">Update Client</button>
        </div>
    </form>
</div>
@endsection
