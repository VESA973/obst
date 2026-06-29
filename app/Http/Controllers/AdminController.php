<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Member;
use App\Models\ResourceFile;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class AdminController extends Controller
{
    public function index(): View
    {
        return view('admin.dashboard', [
            'members' => Member::latest()->get(),
            'files' => ResourceFile::latest()->get(),
            'events' => Event::orderByRaw('event_date IS NULL')->orderBy('event_date')->get(),
            'users' => User::orderByDesc('is_admin')->orderBy('name')->get(),
            'settings' => [
                'maintenance_enabled' => SiteSetting::getValue('maintenance_enabled', '0'),
                'maintenance_message' => SiteSetting::getValue('maintenance_message', 'Le site est temporairement en maintenance. Merci de revenir dans quelques instants.'),
                'admin_note' => SiteSetting::getValue('admin_note', ''),
            ],
        ]);
    }

    public function updateSettings(Request $request): RedirectResponse
    {
        $attributes = $request->validate([
            'maintenance_enabled' => ['nullable', 'boolean'],
            'maintenance_message' => ['required', 'string', 'max:500'],
            'admin_note' => ['nullable', 'string', 'max:1000'],
        ]);

        SiteSetting::setValue('maintenance_enabled', $request->boolean('maintenance_enabled') ? '1' : '0');
        SiteSetting::setValue('maintenance_message', $attributes['maintenance_message']);
        SiteSetting::setValue('admin_note', $attributes['admin_note'] ?? '');

        return back()->with('status', 'Configuration du site mise a jour.');
    }

    public function storeMember(Request $request): RedirectResponse
    {
        Member::create($request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:40'],
            'role' => ['nullable', 'string', 'max:120'],
            'status' => ['required', 'string', 'max:80'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]));

        return back()->with('status', 'Membre ajoute.');
    }

    public function updateMember(Request $request, Member $member): RedirectResponse
    {
        $member->update($request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:40'],
            'role' => ['nullable', 'string', 'max:120'],
            'status' => ['required', 'string', 'max:80'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]));

        return back()->with('status', 'Membre mis a jour.');
    }

    public function destroyMember(Member $member): RedirectResponse
    {
        $member->delete();

        return back()->with('status', 'Membre supprime.');
    }

    public function storeFile(Request $request): RedirectResponse
    {
        $attributes = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'audience' => ['required', Rule::in(['public', 'pro'])],
            'category' => ['nullable', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:2000'],
            'file' => ['required', 'file', 'max:10240'],
        ]);

        $uploadedFile = $request->file('file');
        $attributes['path'] = $uploadedFile->store('resources', 'public');
        $attributes['original_name'] = $uploadedFile->getClientOriginalName();
        unset($attributes['file']);

        ResourceFile::create($attributes);

        return back()->with('status', 'Fichier ajoute.');
    }

    public function destroyFile(ResourceFile $file): RedirectResponse
    {
        Storage::disk('public')->delete($file->path);
        $file->delete();

        return back()->with('status', 'Fichier supprime.');
    }

    public function storeEvent(Request $request): RedirectResponse
    {
        Event::create($this->eventAttributes($request));

        return back()->with('status', 'Evenement ajoute.');
    }

    public function updateEvent(Request $request, Event $event): RedirectResponse
    {
        $event->update($this->eventAttributes($request));

        return back()->with('status', 'Evenement mis a jour.');
    }

    public function destroyEvent(Event $event): RedirectResponse
    {
        $event->delete();

        return back()->with('status', 'Evenement supprime.');
    }

    public function storeUser(Request $request): RedirectResponse
    {
        User::create($request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', Password::min(8)],
            'is_admin' => ['nullable', 'boolean'],
            'is_member' => ['nullable', 'boolean'],
        ]));

        return back()->with('status', 'Utilisateur cree.');
    }

    public function updateUser(Request $request, User $user): RedirectResponse
    {
        $attributes = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user)],
            'password' => ['nullable', Password::min(8)],
            'is_admin' => ['nullable', 'boolean'],
            'is_member' => ['nullable', 'boolean'],
        ]);

        $attributes['is_admin'] = $request->boolean('is_admin');
        $attributes['is_member'] = $request->boolean('is_member');

        if (blank($attributes['password'])) {
            unset($attributes['password']);
        }

        $user->update($attributes);

        return back()->with('status', 'Utilisateur mis a jour.');
    }

    public function destroyUser(Request $request, User $user): RedirectResponse
    {
        abort_if($request->user()->is($user), 422, 'Vous ne pouvez pas supprimer votre propre compte.');

        $user->delete();

        return back()->with('status', 'Utilisateur supprime.');
    }

    private function eventAttributes(Request $request): array
    {
        $attributes = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'event_date' => ['nullable', 'date'],
            'location' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'registration_url' => ['nullable', 'url', 'max:255'],
            'is_published' => ['nullable', 'boolean'],
        ]);

        $attributes['is_published'] = $request->boolean('is_published');

        return $attributes;
    }
}
