<tr>
<td class="header">
<a href="{{ $url }}" style="display: inline-block;">
@if (trim($slot) === 'Laravel')
<img style="width: 336px !important;" src="public/assets/logo.png" class="logo" alt="Logo ZehTicket">
@else
{{ $slot }}
@endif
</a>
</td>
</tr>
