<?php

namespace App\Http\Controllers\Communication;

use App\Http\Controllers\Controller;
use App\Models\PressRelease;
use App\Models\MediaContact;
use Illuminate\Http\Request;

class PublicRelationsController extends Controller
{
    public function pressReleases()
    {
        $releases = PressRelease::with('author')->get();
        return view('pr.press-releases', compact('releases'));
    }

    public function createRelease()
    {
        return view('pr.create_release');
    }

    public function storeRelease(Request $request)
    {
        PressRelease::create($request->all());
        return redirect()->route('pr.press_releases');
    }

    public function contacts()
    {
        $contacts = MediaContact::all();
        return view('pr.media-database', compact('contacts'));
    }

    public function createContact()
    {
        return view('pr.create_contact');
    }

    public function storeContact(Request $request)
    {
        MediaContact::create($request->all());
        return redirect()->route('pr.contacts');
    }

    public function clippings()
    {
        return view('pr.clippings');
    }
}
