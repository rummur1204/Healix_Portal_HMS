<?php

namespace App\Http\Controllers;

use App\Models\SlaConfiguration;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SlaConfigurationController extends Controller
{

    public function index()
    {
        $slas = SlaConfiguration::latest()->get();

        return Inertia::render('SlaConfigurations/Index', [
            'slas' => $slas
        ]);
    }

    public function create()
    {
        return Inertia::render('SlaConfigurations/Create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'sla_name' => 'required|string|max:255',
            'priority' => 'required|in:low,medium,high,critical',
            'first_response_hrs' => 'required|integer|min:1',
            'resolution_hrs' => 'required|integer|min:1',
        ]);

        SlaConfiguration::create($request->all());

        return redirect()->route('sla-configurations.index')
            ->with('success','SLA configuration created');
    }

    public function edit($id)
    {
        $sla = SlaConfiguration::findOrFail($id);

        return Inertia::render('SlaConfigurations/Edit',[
            'sla'=>$sla
        ]);
    }

    public function update(Request $request,$id)
    {
        $sla = SlaConfiguration::findOrFail($id);

        $request->validate([
            'sla_name' => 'required|string|max:255',
            'priority' => 'required|in:low,medium,high,critical',
            'first_response_hrs' => 'required|integer|min:1',
            'resolution_hrs' => 'required|integer|min:1',
        ]);

        $sla->update($request->all());

        return redirect()->route('sla-configurations.index')
            ->with('success','SLA updated');
    }

    public function destroy($id)
    {
        SlaConfiguration::findOrFail($id)->delete();

        return redirect()->back();
    }

}