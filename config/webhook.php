<?php
return ['max_body_bytes'=>(int)env('WEBHOOK_MAX_BODY_BYTES',1048576),'replay_timeout'=>(int)env('WEBHOOK_REPLAY_TIMEOUT',10),'rate_limit'=>(int)env('WEBHOOK_RATE_LIMIT',120)];