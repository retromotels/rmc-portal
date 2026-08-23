<!DOCTYPE html>
<html>
<body style="margin:0;background:#f3ede2;font-family:Arial,Helvetica,sans-serif;color:#2d2837">
  <table width="100%" cellpadding="0" cellspacing="0" style="background:#f3ede2"><tr><td align="center" style="padding:24px">
    <table width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%">
      <tr><td style="background:#1a1526;border-radius:14px 14px 0 0;padding:18px 26px">
        <span style="font-weight:900;letter-spacing:1px;color:#ff5e5b;font-size:15px">RETRO MOTEL COLLECTIVE</span>
      </td></tr>
      <tr><td style="background:#ffffff;border-radius:0 0 14px 14px;padding:26px 28px;font-size:15px;line-height:1.65">
        <h1 style="font-size:20px;margin:0 0 14px">New job application</h1>
        <p style="margin:0 0 14px">You've received an application for <strong>{{ $job->title }}</strong>@if($prop->motel) at <strong>{{ $prop->motel }}</strong>@endif.</p>
        <table cellpadding="0" cellspacing="0" style="width:100%;font-size:14px;border-collapse:collapse;margin:0 0 18px">
          <tr><td style="padding:7px 0;color:#8a8579;width:120px">Applicant</td><td style="padding:7px 0;font-weight:bold">{{ $app->name }}</td></tr>
          <tr><td style="padding:7px 0;color:#8a8579">Email</td><td style="padding:7px 0"><a href="mailto:{{ $app->email }}" style="color:#2f6f76">{{ $app->email }}</a></td></tr>
          @if($app->phone)<tr><td style="padding:7px 0;color:#8a8579">Phone</td><td style="padding:7px 0">{{ $app->phone }}</td></tr>@endif
          <tr><td style="padding:7px 0;color:#8a8579">Role</td><td style="padding:7px 0">{{ $job->title }} ({{ $job->typeLabel() }})</td></tr>
        </table>
        @if($app->message)
          <p style="margin:0 0 6px;color:#8a8579;font-size:13px">Their message:</p>
          <div style="background:#f7f2e8;border-radius:8px;padding:12px 14px;font-size:14px;white-space:pre-line">{{ $app->message }}</div>
        @endif
        <p style="margin:16px 0 0">Log in to the member portal → <strong>Jobs</strong> to see all applicants and any CVs they attached.</p>
      </td></tr>
      <tr><td align="center" style="color:#96907c;font-size:11px;padding:14px">Retro Motel Collective · retromotels.com</td></tr>
    </table>
  </td></tr></table>
</body>
</html>
