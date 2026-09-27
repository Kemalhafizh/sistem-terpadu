<?php

namespace App\Http\Controllers\Communication;

use App\Http\Controllers\Controller;
use App\Models\OmnichannelLog;
use App\Models\OmnichannelTemplate;
use Illuminate\Http\Request;

class OmnichannelController extends Controller
{
    public function dashboard()
    {
        return view('omnichannel.dashboard');
    }

    public function templates()
    {
        $templates = OmnichannelTemplate::all();

        return view('omnichannel.templates', compact('templates'));
    }

    public function createTemplate()
    {
        return view('omnichannel.create_template');
    }

    public function storeTemplate(Request $request)
    {
        OmnichannelTemplate::create($request->all());

        return redirect()->route('omnichannel.templates');
    }

    public function compose()
    {
        return view('omnichannel.composer');
    }

    public function dispatch(Request $request)
    {
        // dispatch logic
        return redirect()->route('omnichannel.dashboard');
    }

    public function logs()
    {
        $logs = OmnichannelLog::with(['user', 'template'])->paginate(15);

        return view('omnichannel.logs', compact('logs'));
    }
}
