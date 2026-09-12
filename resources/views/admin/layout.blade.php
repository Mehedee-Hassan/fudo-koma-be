<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>@yield('title', 'Overview') · Follo Cart</title><link rel="stylesheet" href="/admin.css"></head><body>
<aside class="sidebar"><a class="brand" href="/admin"><span class="logo">f.</span> follo cart<span class="brand-dot">●</span></a><div class="workspace">OPERATIONS WORKSPACE</div><nav>
<a class="{{ request()->is('admin')?'active':'' }}" href="/admin"><span>◫</span> Overview</a>
<div class="nav-label">MANAGE</div>
@foreach(['carts'=>'Food carts','users'=>'People & roles','reports'=>'Moderation','updates'=>'Cart updates','schedules'=>'Schedules','photos'=>'Photos','follows'=>'Following'] as $key=>$label)<a class="{{ request()->is('admin/'.$key.'*')?'active':'' }}" href="/admin/{{ $key }}">{{ $label }}</a>@endforeach
<div class="nav-label">PLATFORM</div>
@foreach(['cart-locations'=>'Cart locations','user-locations'=>'User locations','user-settings'=>'User preferences','notifications'=>'Notification inbox','deliveries'=>'Push deliveries','configuration'=>'Configuration'] as $key=>$label)<a class="{{ request()->is('admin/'.$key.'*')?'active':'' }}" href="/admin/{{ $key }}">{{ $label }}</a>@endforeach
</nav><div class="sidebar-foot"><span class="avatar">{{ strtoupper(substr(auth()->user()->name,0,1)) }}</span><div>{{ auth()->user()->name }}<small>Administrator</small></div><form method="post" action="/logout">@csrf<button class="logout" title="Sign out">↗</button></form></div></aside>
<main><header class="topbar"><div>Workspace <span>/</span> @yield('title', 'Overview')</div><span class="environment">● {{ app()->environment() }}</span></header><div class="content">
@if(session('success'))<div class="notice" role="status">{{ session('success') }}</div>@endif
@if($errors->any())<div class="errors" role="alert">@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>@endif
@yield('content')</div><footer>Follo Cart · Bringing good food closer <span>Admin workspace</span></footer></main></body></html>
