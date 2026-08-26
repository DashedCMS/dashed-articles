{{--
    Elk artikel in een eigen kaart, om dezelfde reden als bij het productblok:
    zonder omhulling loopt de leesknop van het ene item visueel over in het
    beeld van het volgende.

    Een geneste tabel en geen div: Outlook rendert met Word.
--}}
@php $perRij = max(1, (int) $columns); @endphp
<tr><td style="padding:16px 24px;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
        @foreach(array_chunk($articles, $perRij) as $rij)
            <tr>
                @foreach($rij as $article)
                    <td width="{{ (int) (100 / $perRij) }}%" valign="top" style="padding:6px;">
                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border:1px solid #e4e4e7;border-radius:8px;background:#ffffff;">
                            <tr>
                                <td style="padding:0;font-family:Arial,Helvetica,sans-serif;">
                                    @if($article['image'])
                                        <a href="{{ $article['url'] }}" style="display:block;">
                                            <img src="{{ $article['image'] }}" alt="{{ $article['name'] }}" style="width:100%;display:block;border:0;border-top-left-radius:8px;border-top-right-radius:8px;">
                                        </a>
                                    @endif
                                    <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                        <tr><td style="padding:14px 14px 16px;font-family:Arial,Helvetica,sans-serif;">
                                            <a href="{{ $article['url'] }}" style="display:block;font-size:14px;line-height:1.35;font-weight:bold;color:#18181b;text-decoration:none;">{{ $article['name'] }}</a>
                                            @if($article['excerpt'])
                                                <div style="font-size:13px;line-height:1.45;color:#52525b;margin-top:6px;">{{ \Illuminate\Support\Str::limit($article['excerpt'], 100) }}</div>
                                            @endif
                                            <a href="{{ $article['url'] }}" style="display:inline-block;margin-top:12px;padding:9px 16px;background:{{ $primaryColor }};color:{{ $textColor }};text-decoration:none;border-radius:6px;font-size:13px;">Lezen</a>
                                        </td></tr>
                                    </table>
                                </td>
                            </tr>
                        </table>
                    </td>
                @endforeach
                @for($i = count($rij); $i < $perRij; $i++)
                    <td width="{{ (int) (100 / $perRij) }}%">&nbsp;</td>
                @endfor
            </tr>
        @endforeach
    </table>
</td></tr>
