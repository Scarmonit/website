<!-- Modal component for Parker Directory -->
<div id="modal" class="modal" role="dialog" aria-hidden="true" aria-labelledby="modal-title" aria-describedby="modal-content">
    <div class="modal-backdrop" aria-hidden="true"></div>
    <div class="modal-container">
        <div class="modal-header">
            <h3 id="modal-title" class="modal-title">Modal Title</h3>
            <button class="modal-close" aria-label="Close modal">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                    <path d="M2.146 2.854a.5.5 0 1 1 .708-.708L8 7.293l5.146-5.147a.5.5 0 0 1 .708.708L8.707 8l5.147 5.146a.5.5 0 0 1-.708.708L8 8.707l-5.146 5.147a.5.5 0 0 1-.708-.708L7.293 8 2.146 2.854Z"/>
                </svg>
            </button>
        </div>
        <div class="modal-body">
            <div id="modal-content" class="modal-content">
                <!-- Dynamic content will be inserted here -->
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary modal-cancel">Cancel</button>
            <button class="btn btn-primary modal-confirm">Confirm</button>
        </div>
    </div>
</div>