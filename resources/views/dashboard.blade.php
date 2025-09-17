<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold">Panel</h2>
    </x-slot>

    <div class="p-6 space-y-4">
        @role('admin')
        <div class="p-4 rounded" style="background:#F3EEEE;border:1px solid #eee">
            <h3 class="font-bold">Administración</h3>
            <ul class="list-disc ml-5">
                <li><a class="underline" href="{{ route('admin.users.index') }}">Gestión de usuarios</a></li>
                <!-- más accesos sensibles -->
            </ul>
        </div>
        @elserole('employee')
        <div class="p-4 rounded" style="background:#F3EEEE;border:1px solid #eee">
            <h3 class="font-bold">Empleado</h3>
            <p>Accesos a órdenes asignadas, tareas del día, etc.</p>
        </div>
        @elserole('secretary')
        <div class="p-4 rounded" style="background:#F3EEEE;border:1px solid #eee">
            <h3 class="font-bold">Secretaría</h3>
            <p>Recepción, agenda, contacto con clientes…</p>
        </div>
        @else
            <div class="p-4 rounded bg-yellow-50 border">
                No tienes rol asignado. Contacta a la administradora.
            </div>
            @endrole
    </div>
</x-app-layout>
