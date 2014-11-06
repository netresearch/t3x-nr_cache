local db = ARGV[1]
local id_tag_prefix = ARGV[2]
local id_data_prefix = ARGV[3]
local tag_id_prefix = ARGV[4]

redis.call('select', db)

local done = false
local cursor = 0
repeat
    local result = redis.call("SCAN", cursor, 'MATCH', id_tag_prefix .. '*')

    cursor  = result[1]
    local matches = result[2]

    -- execute clean here with matches, take prefixes id_data_prefix and tag_id_prefix

    if cursor == "0" then
        done = true
    end
until done

return done
