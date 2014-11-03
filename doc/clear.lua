-- check given KEYs for existence and cleanup TAG index
-- Return aray with 1 = deleted keys; 2 = checked keys

local db = ARGV[1]
local id_tag_prefix = ARGV[2]
local id_data_prefix = ARGV[3]
local tag_id_prefix = ARGV[4]

redis.call('select', db)

local stats = {}
stats[1] = 0
stats[2] = 0
-- stats[3] = ''

for _,tagkey in ipairs(KEYS) do
    stats[2] = stats[2] + 1
    -- stats[3] = stats[3] .. ' ! ' .. tagkey
    local key = string.gsub(tagkey, id_tag_prefix, '')
    -- stats[3] = stats[3] .. ' key: ' .. key
    -- check identData:KEY for existence
    if 0 == redis.call('EXISTS', id_data_prefix .. key) then

        -- delete KEY entries from tagIdents:TAG hash/list
        for _,tag in ipairs(redis.call('SMEMBERS', tagkey)) do
            -- stats[3] = stats[3] .. ' SREM:' .. tag_id_prefix .. tag .. '#' .. key
            redis.call('SREM', tag_id_prefix .. tag, key)
        end

        -- delete whole identTags:KEY index
        -- stats[3] = stats[3] .. ' DEL:' .. tagkey
        redis.call('DEL', tagkey)
        stats[1] = stats[1] + 1
    end
end

return stats
