<x-mail::message>
# You've been invited to KanFlow!

Hi **{{ $invitee->name }}**,

**{{ $inviter->name }}** has invited you to collaborate on the board **"{{ $board->name }}"** in KanFlow.

You can now view tasks, move cards, and collaborate in real-time with your team.

<x-mail::button :url="config('app.frontend_url') . '/dashboard/board/' . $board->id">
Open Board
</x-mail::button>

If you have any questions or need help, feel free to reply to this email.

Thanks,<br>
The KanFlow Team
</x-mail::message>
