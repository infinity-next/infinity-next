<div class="post-report">
    <div class="report-container">
        @include( 'content.board.post.report', [
            'board'  => $reportedPost->board,
            'post'   => $reportedPost,
            'report' => $report,
        ])
    </div>
</div>
