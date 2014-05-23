package pro.dite;

import java.io.Serializable;
import java.util.HashSet;
import java.util.Hashtable;

public class CacheEntry implements Serializable
{
    final Hashtable<String, HashSet<String>> index = new Hashtable<String, HashSet<String>>();
}
