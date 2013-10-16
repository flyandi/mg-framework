drop function if exists mgSearchSoundsLike;
delimiter $$
create function mgSearchSoundsLike(needle varchar(1024), haystack text, splitChar varchar(1)) returns tinyint
  deterministic
  begin
    declare spacePos int;
    declare searchLen int default length(haystack);
    declare curWord varchar(128) default '';
    declare tempStr text default haystack;
    declare tmp text default '';
    declare soundx1 varchar(1024) default soundex(needle);
    declare soundx2 varchar(1024) default '';
	
    set spacePos = locate(splitChar, tempStr);

    while searchLen > 0 do
      if spacePos = 0 then
        set tmp = tempStr;
        select soundex(tmp) into soundx2;
        if soundx1 = soundx2 then
          return 1;
        else
          return 0;
        end if;
      end if;

      if spacePos != 0 then
        set tmp = substr(tempStr, 1, spacePos-1);
        set soundx2 = soundex(tmp);
        if soundx1 = soundx2 then
          return 1;
        end if;
        set tempStr = substr(tempStr, spacePos+1);
        set searchLen = length(tempStr);
      end if;

      set spacePos = locate(splitChar, tempStr);

    end while;

    return 0;

  end
$$
delimiter ;