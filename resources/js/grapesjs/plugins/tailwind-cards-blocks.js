const cardBlocks = [
    {
        id: 'card-basic',
        label: 'Basic Card',
        media: '<svg viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="14" rx="2" fill="currentColor"/></svg>',
        content: `
<article class="overflow-hidden rounded-lg shadow-sm transition hover:shadow-lg">
  <img alt="" src="https://images.unsplash.com/photo-1524758631624-e2822e304c36?auto=format&fit=crop&q=80&w=1160" class="h-56 w-full object-cover">
  <div class="bg-white p-4 sm:p-6">
    <time datetime="2022-10-10" class="block text-xs text-gray-500">10th Oct 2022</time>
    <a href="#"><h3 class="mt-0.5 text-lg text-gray-900">How to position your furniture for positivity</h3></a>
    <p class="mt-2 line-clamp-3 text-sm leading-relaxed text-gray-500">Lorem ipsum dolor sit amet, consectetur adipisicing elit. Recusandae dolores, possimus pariatur animi temporibus nesciunt praesentium dolore sed nulla ipsum eveniet corporis quidem, mollitia itaque minus soluta, voluptates neque explicabo tempora nisi culpa eius atque dignissimos.</p>
  </div>
</article>
        `,
        category: 'Tailwind Cards',
    },
    {
        id: 'card-border',
        label: 'Border Card',
        media: '<svg viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="14" rx="2" stroke="currentColor" fill="none" stroke-width="2"/></svg>',
        content: `
<article class="overflow-hidden rounded-lg border border-gray-100 bg-white shadow-sm">
  <img alt="" src="https://images.unsplash.com/photo-1600880292203-757bb62b4baf?auto=format&fit=crop&q=80&w=1160" class="h-56 w-full object-cover">
  <div class="p-4 sm:p-6">
    <a href="#"><h3 class="text-lg font-medium text-gray-900">Lorem ipsum dolor sit amet consectetur adipisicing elit.</h3></a>
    <p class="mt-2 line-clamp-3 text-sm leading-relaxed text-gray-500">Lorem ipsum dolor sit amet, consectetur adipisicing elit. Recusandae dolores, possimus pariatur animi temporibus nesciunt praesentium dolore sed nulla ipsum eveniet corporis quidem, mollitia itaque minus soluta, voluptates neque explicabo tempora nisi culpa eius atque dignissimos.</p>
    <a href="#" class="group mt-4 inline-flex items-center gap-1 text-sm font-medium text-blue-600">Find out more <span aria-hidden="true" class="block transition-all group-hover:ml-0.5">-></span></a>
  </div>
</article>
        `,
        category: 'Tailwind Cards',
    },
    {
        id: 'card-tags',
        label: 'Tag Card',
        media: '<svg viewBox="0 0 24 24"><path fill="currentColor" d="M5.5 7A1.5 1.5 0 0 1 4 5.5A1.5 1.5 0 0 1 5.5 4A1.5 1.5 0 0 1 7 5.5A1.5 1.5 0 0 1 5.5 7m15.91 4.58l-9-9C12.05 2.22 11.55 2 11 2H4c-1.11 0-2 .89-2 2v7c0 .55.22 1.05.59 1.41l8.99 9c.37.36.87.59 1.42.59c.55 0 1.05-.23 1.41-.59l7-7c.37-.36.59-.86.59-1.41c0-.56-.23-1.06-.59-1.42Z"/></svg>',
        content: `
<article class="rounded-[10px] border border-gray-200 bg-white px-4 pt-12 pb-4">
  <time datetime="2022-10-10" class="block text-xs text-gray-500">10th Oct 2022</time>
  <a href="#"><h3 class="mt-0.5 text-lg font-medium text-gray-900">How to center an element using JavaScript and jQuery</h3></a>
  <div class="mt-4 flex flex-wrap gap-1">
    <span class="rounded-full bg-purple-100 px-2.5 py-0.5 text-xs whitespace-nowrap text-purple-600">Snippet</span>
    <span class="rounded-full bg-purple-100 px-2.5 py-0.5 text-xs whitespace-nowrap text-purple-600">JavaScript</span>
  </div>
</article>
        `,
        category: 'Tailwind Cards',
    },
    {
        id: 'card-icon',
        label: 'Icon Card',
        media: '<svg viewBox="0 0 24 24"><path fill="currentColor" d="M12 2L2 7v10c0 5.55 3.84 10.74 9 12c5.16-1.26 9-6.45 9-12V7l-10-5Z"/></svg>',
        content: `
<article class="rounded-lg border border-gray-100 bg-white p-4 shadow-sm transition hover:shadow-lg sm:p-6">
  <span class="inline-block rounded-sm bg-blue-600 p-2 text-white">
    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
      <path d="M12 14l9-5-9-5-9 5 9 5z"></path>
      <path d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"></path>
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222"></path>
    </svg>
  </span>
  <a href="#"><h3 class="mt-0.5 text-lg font-medium text-gray-900">Lorem ipsum dolor sit, amet consectetur adipisicing elit.</h3></a>
  <p class="mt-2 line-clamp-3 text-sm leading-relaxed text-gray-500">Lorem ipsum dolor sit amet, consectetur adipisicing elit. Recusandae dolores, possimus pariatur animi temporibus nesciunt praesentium dolore sed nulla ipsum eveniet corporis quidem, mollitia itaque minus soluta, voluptates neque explicabo tempora nisi culpa eius atque dignissimos.</p>
  <a href="#" class="group mt-4 inline-flex items-center gap-1 text-sm font-medium text-blue-600">Find out more <span aria-hidden="true" class="block transition-all group-hover:ml-0.5">-></span></a>
</article>
        `,
        category: 'Tailwind Cards',
    },
    {
        id: 'card-horizontal',
        label: 'Horizontal Card',
        media: '<svg viewBox="0 0 24 24"><rect x="2" y="6" width="20" height="12" rx="2" fill="currentColor"/><line x1="10" y1="6" x2="10" y2="18" stroke="white" stroke-width="2"/></svg>',
        content: `
<article class="flex bg-white transition hover:shadow-xl">
  <div class="rotate-180 p-2 [writing-mode:vertical-lr]">
    <time datetime="2022-10-10" class="flex items-center justify-between gap-4 text-xs font-bold text-gray-900 uppercase">
      <span>2022</span><span class="w-px flex-1 bg-gray-900/10"></span><span>Oct 10</span>
    </time>
  </div>
  <div class="hidden sm:block sm:basis-56"><img alt="" src="https://images.unsplash.com/photo-1609557927087-f9cf8e88de18?auto=format&fit=crop&q=80&w=1160" class="aspect-square h-full w-full object-cover"></div>
  <div class="flex flex-1 flex-col justify-between">
    <div class="border-l border-gray-900/10 p-4 sm:border-l-transparent sm:p-6">
      <a href="#"><h3 class="font-bold text-gray-900 uppercase">Finding the right guitar for your style - 5 tips</h3></a>
      <p class="mt-2 line-clamp-3 text-sm leading-relaxed text-gray-700">Lorem ipsum dolor sit amet, consectetur adipisicing elit. Recusandae dolores, possimus pariatur animi temporibus nesciunt praesentium dolore sed nulla ipsum eveniet corporis quidem, mollitia itaque minus soluta, voluptates neque explicabo tempora nisi culpa eius atque dignissimos.</p>
    </div>
    <div class="sm:flex sm:items-end sm:justify-end"><a href="#" class="block bg-yellow-300 px-5 py-3 text-center text-xs font-bold text-gray-900 uppercase transition hover:bg-yellow-400">Read Blog</a></div>
  </div>
</article>
        `,
        category: 'Tailwind Cards',
    },
    {
        id: 'card-overlay',
        label: 'Overlay Card',
        media: '<svg viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="14" rx="2" fill="currentColor"/><rect x="3" y="13" width="18" height="6" fill="rgba(0,0,0,0.5)"/></svg>',
        content: `
<article class="relative overflow-hidden rounded-lg shadow-sm transition hover:shadow-lg">
  <img alt="" src="https://images.unsplash.com/photo-1661956602116-aa6865609028?auto=format&fit=crop&q=80&w=1160" class="absolute inset-0 h-full w-full object-cover">
  <div class="relative bg-gradient-to-t from-gray-900/50 to-gray-900/25 pt-32 sm:pt-48 lg:pt-64">
    <div class="p-4 sm:p-6">
      <time datetime="2022-10-10" class="block text-xs text-white/90">10th Oct 2022</time>
      <a href="#"><h3 class="mt-0.5 text-lg text-white">How to position your furniture for positivity</h3></a>
      <p class="mt-2 line-clamp-3 text-sm leading-relaxed text-white/95">Lorem ipsum dolor sit amet, consectetur adipisicing elit. Recusandae dolores, possimus pariatur animi temporibus nesciunt praesentium dolore sed nulla ipsum eveniet corporis quidem, mollitia itaque minus soluta, voluptates neque explicabo tempora nisi culpa eius atque dignissimos.</p>
    </div>
  </div>
</article>
        `,
        category: 'Tailwind Cards',
    },
    {
        id: 'card-author',
        label: 'Author Card',
        media: '<svg viewBox="0 0 24 24"><circle cx="12" cy="8" r="3" fill="currentColor"/><path fill="currentColor" d="M12 14c-4 0-8 2-8 4v2h16v-2c0-2-4-4-8-4Z"/></svg>',
        content: `
<a href="#" class="block rounded-md border border-gray-300 p-4 shadow-sm sm:p-6">
  <div class="sm:flex sm:justify-between sm:gap-4 lg:gap-6">
    <div class="sm:order-last sm:shrink-0"><img alt="" src="https://images.unsplash.com/photo-1633332755192-727a05c4013d?auto=format&fit=crop&q=80&w=1160" class="h-16 w-16 rounded-full object-cover sm:h-20 sm:w-20"></div>
    <div class="mt-4 sm:mt-0">
      <h3 class="text-lg font-medium text-gray-900">How I built my first website with Nuxt, Tailwind CSS and Vercel</h3>
      <p class="mt-1 text-sm text-gray-700">By John Doe</p>
      <p class="mt-4 line-clamp-2 text-sm text-gray-700">Lorem ipsum dolor sit, amet consectetur adipisicing elit. At velit illum provident a, ipsa maiores deleniti consectetur nobis et eaque.</p>
    </div>
  </div>
  <dl class="mt-6 flex gap-4 lg:gap-6">
    <div class="flex items-center gap-2"><dt class="text-gray-700"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-5 w-5"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5"></path></svg></dt><dd class="text-xs text-gray-700">31/06/2025</dd></div>
    <div class="flex items-center gap-2"><dt class="text-gray-700"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-5 w-5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25"></path></svg></dt><dd class="text-xs text-gray-700">12 minutes</dd></div>
  </dl>
</a>
        `,
        category: 'Tailwind Cards',
    },
    {
        id: 'card-profile',
        label: 'Profile Card',
        media: '<svg viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="14" rx="2" fill="currentColor" opacity="0.7"/><text x="12" y="15" text-anchor="middle" fill="white" font-size="8">Profile</text></svg>',
        content: `
<a href="#" class="group relative block bg-black">
  <img alt="" src="https://images.unsplash.com/photo-1603871165848-0aa92c869fa1?auto=format&fit=crop&q=80&w=1160" class="absolute inset-0 h-full w-full object-cover opacity-75 transition-opacity group-hover:opacity-50">
  <div class="relative p-4 sm:p-6 lg:p-8">
    <p class="text-sm font-medium tracking-widest text-pink-500 uppercase">Developer</p>
    <p class="text-xl font-bold text-white sm:text-2xl">Tony Wayne</p>
    <div class="mt-32 sm:mt-48 lg:mt-64"><div class="translate-y-8 transform opacity-0 transition-all group-hover:translate-y-0 group-hover:opacity-100"><p class="text-sm text-white">Lorem ipsum dolor, sit amet consectetur adipisicing elit. Omnis perferendis hic asperiores quibusdam quidem voluptates doloremque reiciendis nostrum harum. Repudiandae?</p></div></div>
  </div>
</a>
        `,
        category: 'Tailwind Cards',
    },
    {
        id: 'card-brutalist',
        label: 'Brutalist Card',
        media: '<svg viewBox="0 0 24 24"><rect x="4" y="6" width="16" height="12" rx="1" stroke="currentColor" fill="none" stroke-width="2" stroke-dasharray="4 2"/><rect x="5" y="7" width="16" height="12" rx="1" fill="currentColor"/></svg>',
        content: `
<a href="#" class="group relative block h-64 sm:h-80 lg:h-96">
  <span class="absolute inset-0 border-2 border-dashed border-black"></span>
  <div class="relative flex h-full transform items-end border-2 border-black bg-white transition-transform group-hover:-translate-x-2 group-hover:-translate-y-2">
    <div class="px-4 pb-4 transition-opacity group-hover:absolute group-hover:opacity-0 sm:px-6 sm:pb-4 lg:px-8 lg:pb-8">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 sm:h-12 sm:w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
      <h2 class="mt-4 text-xl font-medium sm:text-2xl">Go around the world</h2>
    </div>
    <div class="absolute p-4 opacity-0 transition-opacity group-hover:relative group-hover:opacity-100 sm:p-6 lg:p-8"><h3 class="mt-4 text-xl font-medium sm:text-2xl">Go around the world</h3><p class="mt-4 text-sm sm:text-base">Lorem ipsum dolor sit amet consectetur adipisicing elit. Cupiditate, praesentium voluptatem omnis atque culpa repellendus.</p><p class="mt-8 font-bold">Read more</p></div>
  </div>
</a>
        `,
        category: 'Tailwind Cards',
    },
    {
        id: 'card-property',
        label: 'Property Card',
        media: '<svg viewBox="0 0 24 24"><path fill="currentColor" d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8h5Z"/></svg>',
        content: `
<a href="#" class="block rounded-lg p-4 shadow-sm shadow-indigo-100">
  <img alt="" src="https://images.unsplash.com/photo-1613545325278-f24b0cae1224?auto=format&fit=crop&q=80&w=1160" class="h-56 w-full rounded-md object-cover">
  <div class="mt-2">
    <dl><div><dd class="text-sm text-gray-500">$240,000</dd></div><div><dd class="font-medium">123 Wallaby Avenue, Park Road</dd></div></dl>
    <div class="mt-6 flex items-center gap-8 text-xs">
      <div class="sm:inline-flex sm:shrink-0 sm:items-center sm:gap-2"><svg class="h-4 w-4 text-indigo-700" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z"></path></svg><div class="mt-1.5 sm:mt-0"><p class="text-gray-500">Parking</p><p class="font-medium">2 spaces</p></div></div>
      <div class="sm:inline-flex sm:shrink-0 sm:items-center sm:gap-2"><svg class="h-4 w-4 text-indigo-700" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path></svg><div class="mt-1.5 sm:mt-0"><p class="text-gray-500">Bathroom</p><p class="font-medium">2 rooms</p></div></div>
      <div class="sm:inline-flex sm:shrink-0 sm:items-center sm:gap-2"><svg class="h-4 w-4 text-indigo-700" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path></svg><div class="mt-1.5 sm:mt-0"><p class="text-gray-500">Bedroom</p><p class="font-medium">4 rooms</p></div></div>
    </div>
  </div>
</a>
        `,
        category: 'Tailwind Cards',
    },
    {
        id: 'card-podcast',
        label: 'Podcast Card',
        media: '<svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="2" fill="currentColor"/><path fill="currentColor" d="M12 15c-1.66 0-3-1.34-3-3s1.34-3 3-3 3 1.34 3 3-1.34 3-3 3m0-8c-2.76 0-5 2.24-5 5s2.24 5 5 5 5-2.24 5-5-2.24-5-5-5Z"/></svg>',
        content: `
<article class="rounded-xl bg-white p-4 ring-4 ring-indigo-50 sm:p-6 lg:p-8">
  <div class="flex items-start sm:gap-8">
    <div class="hidden sm:grid sm:h-20 sm:w-20 sm:shrink-0 sm:place-content-center sm:rounded-full sm:border-2 sm:border-indigo-500" aria-hidden="true"><div class="flex items-center gap-1"><span class="h-8 w-0.5 rounded-full bg-indigo-500"></span><span class="h-6 w-0.5 rounded-full bg-indigo-500"></span><span class="h-4 w-0.5 rounded-full bg-indigo-500"></span><span class="h-6 w-0.5 rounded-full bg-indigo-500"></span><span class="h-8 w-0.5 rounded-full bg-indigo-500"></span></div></div>
    <div>
      <strong class="rounded-sm border border-indigo-500 bg-indigo-500 px-3 py-1.5 text-[10px] font-medium text-white">Episode #101</strong>
      <h3 class="mt-4 text-lg font-medium sm:text-xl"><a href="#" class="hover:underline">Some Interesting Podcast Title</a></h3>
      <p class="mt-1 text-sm text-gray-700">Lorem ipsum, dolor sit amet consectetur adipisicing elit. Ipsam nulla amet voluptatum sit rerum, atque, quo culpa ut necessitatibus eius suscipit eum accusamus, aperiam voluptas exercitationem facere aliquid fuga. Sint.</p>
      <div class="mt-4 sm:flex sm:items-center sm:gap-2"><div class="flex items-center gap-1 text-gray-500"><svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg><p class="text-xs font-medium">48:32 minutes</p></div><span class="hidden sm:block" aria-hidden="true">·</span><p class="mt-2 text-xs font-medium text-gray-500 sm:mt-0">Featuring <a href="#" class="underline hover:text-gray-700">Barry</a>, <a href="#" class="underline hover:text-gray-700">Sandra</a> and <a href="#" class="underline hover:text-gray-700">August</a></p></div>
    </div>
  </div>
</article>
        `,
        category: 'Tailwind Cards',
    },
    {
        id: 'card-discussion',
        label: 'Discussion Card',
        media: '<svg viewBox="0 0 24 24"><path fill="currentColor" d="M20 2H4c-1.1 0-2 .9-2 2v18l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2Z"/></svg>',
        content: `
<article class="rounded-xl border-2 border-gray-100 bg-white">
  <div class="flex items-start gap-4 p-4 sm:p-6 lg:p-8">
    <a href="#" class="block shrink-0"><img alt="" src="https://images.unsplash.com/photo-1570295999919-56ceb5ecca61?auto=format&fit=crop&q=80&w=1160" class="h-14 w-14 rounded-lg object-cover"></a>
    <div>
      <h3 class="font-medium sm:text-lg"><a href="#" class="hover:underline">Question about Rendering</a></h3>
      <p class="line-clamp-2 text-sm text-gray-700">Lorem ipsum dolor, sit amet consectetur adipisicing elit. Accusamus, accusantium temporibus iure delectus ut totam natus nesciunt ex? Ducimus, enim.</p>
      <div class="mt-2 sm:flex sm:items-center sm:gap-2"><div class="flex items-center gap-1 text-gray-500"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z"></path></svg><p class="text-xs">14 comments</p></div><span class="hidden sm:block" aria-hidden="true">·</span><p class="hidden sm:block sm:text-xs sm:text-gray-500">Posted by <a href="#" class="font-medium underline hover:text-gray-700">John</a></p></div>
    </div>
  </div>
  <div class="flex justify-end"><strong class="-mr-0.5 -mb-0.5 inline-flex items-center gap-1 rounded-tl-xl rounded-br-xl bg-green-600 px-3 py-1.5 text-white"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path></svg><span class="text-[10px] font-medium sm:text-xs">Solved!</span></strong></div>
</article>
        `,
        category: 'Tailwind Cards',
    },
];

export default function tailwindCardsBlocksPlugin(editor) {
    if (editor.__tailwindCardsBlocksReady) {
        return;
    }
    editor.__tailwindCardsBlocksReady = true;

    const blockManager = editor.BlockManager;

    cardBlocks.forEach((block) => {
        if (blockManager.get(block.id)) {
            blockManager.remove(block.id);
        }

        blockManager.add(block.id, {
            label: block.label,
            media: block.media,
            content: block.content,
            category: {
                label: block.category,
                open: true,
            },
            attributes: {
                class: 'gjs-block-card',
            },
        });
    });
}

