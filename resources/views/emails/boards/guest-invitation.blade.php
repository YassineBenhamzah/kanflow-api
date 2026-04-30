<x-mail::message>
# You've been invited to KanFlow!

Hi there,

**{{ $inviter->name }}** has invited you to collaborate on the board **"{{ $board->name }}"** in KanFlow.

KanFlow is a modern, real-time collaboration tool to manage tasks and projects.

To accept this invitation and join the board, please click the button below:

<x-mail::button :url="env('FRONTEND_URL', 'http://localhost:3000') . '/invite?token=' . $token">
Accept Invitation
</x-mail::button>

If you don't have an account yet, you will be able to create one quickly.

Thanks,<br>
The KanFlow Team
</x-mail::message>
