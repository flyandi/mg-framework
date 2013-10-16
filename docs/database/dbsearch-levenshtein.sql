drop function if exists mgSearchLevensthein;
drop function if exists mgSearchLevenstheinRatio;
delimiter $$

create function mgSearchLevensthein( s1 varchar(255), s2 varchar(255) ) returns int
  deterministic
  begin
    declare s1_len, s2_len, i, j, c, c_temp, cost int;
    declare s1_char char;
    -- max strlen=255
    declare cv0, cv1 varbinary(256);
    set s1_len = char_length(s1), s2_len = char_length(s2), cv1 = 0x00, j = 1, i = 1, c = 0;
    if s1 = s2 then
      return 0;
    elseif s1_len = 0 then
      return s2_len;
    elseif s2_len = 0 then
      return s1_len;
    else
      while j <= s2_len do
        set cv1 = concat(cv1, unhex(hex(j))), j = j + 1;
      end while;
      while i <= s1_len do
        set s1_char = substring(s1, i, 1), c = i, cv0 = unhex(hex(i)), j = 1;
        while j <= s2_len do
          set c = c + 1;
          if s1_char = substring(s2, j, 1) then 
            set cost = 0; else set cost = 1;
          end if;
          set c_temp = conv(hex(substring(cv1, j, 1)), 16, 10) + cost;
          if c > c_temp then set c = c_temp; end if;
            set c_temp = conv(hex(substring(cv1, j+1, 1)), 16, 10) + 1;
            if c > c_temp then 
              set c = c_temp; 
            end if;
            set cv0 = concat(cv0, unhex(hex(c))), j = j + 1;
        end while;
        set cv1 = cv0, i = i + 1;
      end while;
    end if;
    return c;
  end;

$$

create function mgSearchLevenstheinRatio( s1 varchar(255), s2 varchar(255) )
  returns int
  deterministic
  begin
    declare s1_len, s2_len, max_len int;
    set s1_len = length(s1), s2_len = length(s2);
    if s1_len > s2_len then 
      set max_len = s1_len; 
    else 
      set max_len = s2_len; 
    end if;
    return round((1 - mgSearchLevensthein(s1, s2) / max_len) * 100);
  end; 
  
$$
delimiter ;