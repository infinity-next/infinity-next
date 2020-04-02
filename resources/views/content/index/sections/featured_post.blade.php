@if ($featured && $featured instanceof \App\Post)
<section id="site-featured-post" class="grid-100">
    <div class="smooth-box">
        @include('content.board.post', [
            'board' => $featured->board,
            'post'  => $featured,
            'crown' => true,
        ])
    </div>
</section>
@endif
